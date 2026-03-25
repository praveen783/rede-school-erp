<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX – List Active Boards
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $boards = Board::where('is_active', 1)
                        ->orderBy('name')
                        ->get();

        return response()->json([
            'data' => $boards
        ]);
    }
}