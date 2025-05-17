<?php

namespace App\Http\Controllers;

use App\Models\Wing;
use Illuminate\Http\Request;

class WingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $wings = Wing::paginate(10);
        return view('wings.index', compact('wings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('wings.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:wings,name|max:255',
        ]);

        Wing::create([
            'name' => $request->name,
        ]);

        return redirect()->route('wings.index')->with('toast_success', 'Wing created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $wing = Wing::findOrFail($id);
        return view('wings.show', compact('wing'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $wing = Wing::findOrFail($id);
        return view('wings.edit', compact('wing'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $wing = Wing::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:wings,name,' . $wing->id,
        ]);

        $wing->update([
            'name' => $request->name,
        ]);

        return redirect()->route('wings.index')->with('toast_success', 'Wing updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $wing = Wing::findOrFail($id);
        $wing->delete();

        return redirect()->route('wings.index')->with('toast_success', 'Wing deleted successfully.');
    }
}
