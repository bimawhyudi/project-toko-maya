<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        return match ($request->user()->role) {
            'kasir' => redirect()->route('kasir.index'),
            'admin_gudang' => redirect()->route('gudang.index'),
            'pemilik' => redirect()->route('pemilik.index'),
            default => redirect()->route('beranda'),
        };
    }
}