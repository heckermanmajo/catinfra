<?php

class transform_lib {

    static function write_analysis_result_into_database(
        string $result_type,
        string $source_file,
        string|array $content,
        string $trace,
        string $logs,
        int $community_id,
        int|bool $success
    )
    {

        $content = is_array($content) ? json_encode($content) : $content;

        lib::insert(
            "AnalysisResultData",
            [
                "result_type" => $result_type,
                "source_file_name" => $source_file,
                "content" => $content,
                "logs" => $logs,
                "trace" => $trace,
                "community_id" => $community_id,
                "success" => $success ? 1 : 0,
            ]
        );

    }

}