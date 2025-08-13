<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BkashValidationResultController extends Controller
{
    /**
     * Handle the callback response from bKash.
     */
    public function handleResult(Request $request)
    {
        Log::info('bKash Result Received', $request->all());

        // {
        //     "Response": {
        //       "ResponseCode": "0000",
        //       "ResponseDesc": "Validation successful",
        //       "OriginatorConversationID": "CustomerValidation_65fd3a2c",
        //       "ConversationID": "AG_20240213_123456789",
        //       "TransactionID": "TRX123456789",
        //       "ReceiverParty": {
        //         "IdentifierType": 1,
        //         "Identifier": "017XXXXXXXX"
        //       },
        //       "ValidationResult": {
        //         "Status": "Approved",
        //         "CustomerName": "John Doe",
        //         "CustomerAccountType": "Personal",
        //         "AdditionalInfo": "Verified"
        //       }
        //     }
        //   }
          
        
        // Process the response
        $responseData = $request->all();
        
        // Validate and store the response as needed
        // Example: Store in database or trigger additional processing

        return response()->json(['message' => 'Callback received'], 200);
    }
    
    public function handleBeneficiaryAccountResult(Request $request)
    {
        Log::info('bKash Result Received', $request->all());

        // {
        //     "Response": {
        //       "ResponseCode": "0000",
        //       "ResponseDesc": "Validation successful",
        //       "OriginatorConversationID": "CustomerValidation_65fd3a2c",
        //       "ConversationID": "AG_20240213_123456789",
        //       "TransactionID": "TRX123456789",
        //       "ReceiverParty": {
        //         "IdentifierType": 1,
        //         "Identifier": "017XXXXXXXX"
        //       },
        //       "ValidationResult": {
        //         "Status": "Approved",
        //         "CustomerName": "John Doe",
        //         "CustomerAccountType": "Personal",
        //         "AdditionalInfo": "Verified"
        //       }
        //     }
        //   }
          
        
        // Process the response
        $responseData = $request->all();
        
        // Validate and store the response as needed
        // Example: Store in database or trigger additional processing

        return response()->json(['message' => 'Callback received'], 200);
    }
}
