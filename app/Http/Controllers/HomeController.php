<?php

namespace App\Http\Controllers; // Correct Namespace (Uppercase App)

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class HomeController extends Controller
{
    public function updateButtonData(Request $request)
    {
        $buttonId = $request->input('id');
        $weekNum = $request->input('weekNum');
        $dayOfWeek = $request->input('dayOfWeek'); // May be null

        $jsonDataPath = resource_path('data.json');
        $jsonData = json_decode(File::get($jsonDataPath), true);

        foreach ($jsonData as &$button) {
            if ($button['ID'] == $buttonId) {
                $button['WeekNum'] = $weekNum;
                if ($dayOfWeek !== null) {
                    $button['DayOfWeek'] = $dayOfWeek;
                } else {
                    unset($button['DayOfWeek']); // Remove DayOfWeek if not selected
                }
                break; // Stop once the button is found and updated
            }
        }

        File::put($jsonDataPath, json_encode($jsonData, JSON_PRETTY_PRINT)); // Write back to data.json with pretty formatting

        return response()->json(['message' => 'data.json updated successfully']);
    }
}

// Add this route definition if it's not already in your routes file
Route::post('/update-button-data', [HomeController::class, 'updateButtonData']);