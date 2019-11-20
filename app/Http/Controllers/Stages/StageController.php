<?php

namespace App\Http\Controllers\Stages;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Stages\Stage;

class StageController extends Controller
{
    public function show ($stage)
    {
        return Stage::with('authorizedByUser', 'comments')->findOrFail($stage);
    }

    public function store (Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'transactionId' => 'required|integer',
        ]);

        $stage = Stage::create([
            'name' => $request->name,
            'transaction_id' => $request->transactionId
        ]);

        return $this->show($stage->id);
    }

    public function update (Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string'
        ]);

        $stage = Stage::find($request->id);

        if ($stage->name != $request->name) {
            $stage->name = $request->name;
            $stage->save();
        }

        return $stage;
    }

    public function destroy ($stage)
    {
        return Stage::destroy($stage);
    }

    public function toggleAuthorized (Request $request)
    {
        $this->validate($request, [
            'authorized' => 'required|boolean'
        ]);

        $stage = Stage::find($request->id);

        if ($stage->authorized != $request->authorized) {
            $stage->authorized = $request->authorized;
            if ($request->authorized) {
                $stage->authorized_by = Auth::id();
                $stage->authorized_at = now();
            } else {
                $stage->authorized_by = null;
                $stage->authorized_at = null;
            }
            $stage->save();
        }

        return $this->show($stage->id);
    }
}