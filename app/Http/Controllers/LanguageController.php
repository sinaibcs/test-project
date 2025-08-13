<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;


class LanguageController extends Controller
{

public function getLanguageDataBn()
{
    $languageFilePath = resource_path('lang/bn.json');

    if (File::exists($languageFilePath)) {
        $languageData = json_decode(File::get($languageFilePath), true);
        return response()->json(['languageData' => $languageData], 200);
    } else {
        return response()->json(['message' => 'Language file not found'], 404);
    }
}
 public function LangStoreBn(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'languageData' => 'required|array',
        ]);

        // Path to the language file you want to update
        $languageFilePath = resource_path('lang/bn.json');

        // Update the language file with the new data
        if (File::exists($languageFilePath)) {
            File::put($languageFilePath, json_encode($validated['languageData'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return response()->json(['message' => 'Language data saved successfully'], 200);
        } else {
            return response()->json(['message' => 'Language file not found'], 404);
        }
 }

 public function getLanguageDataEn()
{
    $languageFilePath = resource_path('lang/en.json');

    if (File::exists($languageFilePath)) {
        $languageData = json_decode(File::get($languageFilePath), true);
        return response()->json(['languageData' => $languageData], 200);
    } else {
        return response()->json(['message' => 'Language file not found'], 404);
    }
}
 public function LangStoreEn(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'languageData' => 'required|array',
        ]);

        // Path to the language file you want to update
        $languageFilePath = resource_path('lang/en.json');

        // Update the language file with the new data
        if (File::exists($languageFilePath)) {
            File::put($languageFilePath, json_encode($validated['languageData'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return response()->json(['message' => 'Language data saved successfully'], 200);
        } else {
            return response()->json(['message' => 'Language file not found'], 404);
        }
  }
}