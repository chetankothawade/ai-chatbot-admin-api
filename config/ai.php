<?php

return [
    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'transcription_model' => env('OPENAI_TRANSCRIPTION_MODEL', 'gpt-4o-mini-transcribe'),
];
