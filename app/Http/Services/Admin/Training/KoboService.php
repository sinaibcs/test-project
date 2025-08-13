<?php

namespace App\Http\Services\Admin\Training;

use GuzzleHttp\Client;

class KoboService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://kf.kobotoolbox.org/api/v2/',
        ]);
    }

    public function getData($endpoint, $token)
    {
        $response = $this->client->get($endpoint, [
            'headers' => [
                'Authorization' => 'Token ' . $token,
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }


    public function getAssessmentData($formId, $token)
    {
//        $formId = "aoh7beyhUxZ2yAMCJjgCwW";
//        $endpoint = 'assets/' . $formId . '/data?format=json&query={"Employee_ID" : "719"}';
        $endpoint = 'assets/' . $formId . '/data?format=json';

        $data = $this->getData($endpoint, $token);
        return $data['results'] ?? [];
    }


    public function insertQuestion($program, $token)
    {
        $endpoint = 'assets/' . $program->form_id . '?format=json';
        $examResults = $this->getData($endpoint, $token);

        $questionPaper = [];
        $notInType = ["start", "end", "begin_group", "end_group", "note", "calculate", "geoshape"];
        $notInName = ['_version_'];

        $survey = $examResults['content']['survey'] ?? [];

        foreach ($survey as $question) {
            $label = $question['label'][0] ?? null;
            $autoName = $question['$autoname'] ?? null;
            $type = $question['type'] ?? null;

            if (!in_array($type, $notInType) && !in_array($autoName, $notInName)) {
                $questionPaper[] = [
                    'label' => $label,
                    '$autoname' => $autoName,
                    'type' => $type,
                ];
            }
        }

        $program->question_paper = $questionPaper;
        $program->save();

        return $program;
    }


    public function insertAnswers($program, $token)
    {
        $endpoint = 'assets/' . $program->form_id . '/data?format=json';
        $data = $this->getData($endpoint, $token);
        $examResults = $data['results'] ?? [];

        foreach ($examResults as $result) {
            if (isset($result['Employee_ID']) &&
                ($user = $program->participants()->where('user_id', (int)$result['Employee_ID'])->first())) {

                $passcodeTrue = isset($result['Passcode']) ?
                    $result['Passcode'] == $user->passcode : false;

                $passcodeTrue = true;

                if (!$user->exam_response && $passcodeTrue) {
                    $user->exam_response = $result;
                    $user->save();
                }
            }
        }
    }



    public function insertTrainingPaper($program, $token)
    {
        $endpoint = 'assets/' . $program->training_form_id . '?format=json';
        $examResults = $this->getData($endpoint, $token);

        $questionPaper = [];
        $notInType = ["start", "end", "begin_group", "end_group", "note", "calculate", "geoshape"];
        $notInName = ['_version_'];

        $survey = $examResults['content']['survey'] ?? [];

        foreach ($survey as $question) {
            $label = $question['label'][0] ?? null;
            $autoName = $question['$autoname'] ?? null;
            $type = $question['type'] ?? null;

            if (!in_array($type, $notInType) && !in_array($autoName, $notInName)) {
                $questionPaper[] = [
                    'label' => $label,
                    '$autoname' => $autoName,
                    'type' => $type,
                ];
            }
        }

        $program->trainer_ratings_paper = $questionPaper;
        $program->save();

        return $program;
    }



    public function insertTrainerRatingAnswers($program, $token)
    {
        $endpoint = 'assets/' . $program->training_form_id . '/data?format=json';
        $data = $this->getData($endpoint, $token);
        $examResults = $data['results'] ?? [];

        foreach ($examResults as $result) {
            if (isset($result['Employee_ID']) &&
                ($user = $program->participants()->where('user_id', (int)$result['Employee_ID'])->first())) {

                if (!$user->trainer_rating_response) {
                    $user->trainer_rating_response = $result;
                    $user->save();
                }
            }
        }
    }




}
