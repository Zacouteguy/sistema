<?php

namespace App\Http\Controllers;



use Illuminate\Http\Request;

use App\Models\Event;
use App\Models\User;


class EventController extends Controller
{

    public function index() {

        $search = request('search');
        if ($search){

            // like para buscar qualquer coisa, % pra frente ou pra tras, array dentro de array por causa de eventos privados logo mais ja ficam tudo pifado
            $events = Event::where([
                ['title', 'like', '%' .$search.'%']
                ])->get();// digo que é pra pegar pelo metodo get

        } else {

            $events = Event::all();

        }



        return view('welcome',['events' => $events, 'search' => $search]);

    }

    public function create() {
        return view('events.create');
    }

    public function store(Request $request) {

        $event = new Event;

        $event->title = $request->title;
        $event->date = $request->date;
        $event->city = $request->city;
        $event->private = $request->private;
        $event->description = $request->description;
        $event->items = $request->items;  // itens com chek box do json, mas vou precisar dizer para o laravel que não é string,  é um array, vou fazer uma alteração no model

        // Image Upload
        if($request->hasFile('image') && $request->file('image')->isValid()) {

            $requestImage = $request->image;

            $extension = $requestImage->extension();

            $imageName = md5($requestImage->getClientOriginalName() . strtotime("now")) . "." . $extension;

            $requestImage->move(public_path('img/events'), $imageName);

            $event->image = $imageName;

        }

        $user = auth()->user();
        $event->user_id = $user->id;

        $event->save();

        return redirect('/')->with('msg', ' SEISC informa: Evento criado com Sucesso!');

    }


    public function show($id) {

        $event = Event::findOrFail($id);

        $eventOwner = User::where('id', $event->user_id)->first()->toArray();

        return view('events.show', ['event' => $event, 'eventOwner' => $eventOwner]);

    }


    public function dashboard() {

        $user = auth()->user();

        $events = $user->events;

        $eventsAsParticipant = $user->eventsAsParticipant;

        return view('events.dashboard',
            ['events' => $events, 'eventsasparticipant' => $eventsAsParticipant]
        );

    }

    public function destroy($id) {
        Event::findOrFail($id)->delete();
        return redirect('/dashboard')->with('msg', ' SEISC Informa: Evento Excluído com Sucesso!');


    }
    public function edit($id) {

        $event = Event::findOrFail($id);

        return view('events.edit', ['event' => $event]);

    }

    public function update(Request $request) {

        $data = $request->all();

        // Image Upload
        if($request->hasFile('image') && $request->file('image')->isValid()) {

            $requestImage = $request->image;

            $extension = $requestImage->extension();

            $imageName = md5($requestImage->getClientOriginalName() . strtotime("now")) . "." . $extension;

            $requestImage->move(public_path('img/events'), $imageName);

            $data['image'] = $imageName;

        }

        Event::findOrFail($request->id)->update($data);

        return redirect('/dashboard')->with('msg', 'SEISC Informa: Evento editado com sucesso!');

    }

    public function joinEvent($id) {

        $user = auth()->user();

        $user->eventsAsParticipant()->attach($id);

        $event = Event::findOrFail($id);

        return redirect('/dashboard')->with('msg', 'CEISC Informa: Sua presença está confirmada no evento ' . $event->title);

    }

}



