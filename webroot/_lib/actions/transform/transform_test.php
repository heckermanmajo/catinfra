<?php

    /**
     * @throws Throwable
     */
    function transform_test(int $community_id): array
    {

        ob_start();

        try
        {

            echo "Hello World -> tets logging!";

            $data = lib::select(
                "SELECT * FROM `RawDataPage` WHERE community_id = :community_id and type = :about",
                [":community_id" => $community_id]
            );

            # get some data from it and return it
            $about = $data["pageProps"]["currentGroup"]["metadata"]["lpDescription"];

            $result = [
                "about" => $about,
            ];

            # todo: event log here ...

            transform_lib::write_analysis_result_into_database(
                result_type: "transform_test",
                source_file: __FILE__,
                content: $result,
                trace: "",
                logs: ob_get_clean(),
                community_id: $community_id,
                success: true,
            );

            return $result;

        }
        catch (Throwable $t)
        {
            transform_lib::write_analysis_result_into_database(
                result_type: "transform_test",
                source_file: __FILE__,
                content: [],
                trace: $t->getMessage() . "\n<br>" . $t->getTraceAsString(),
                logs: ob_get_clean(),
                community_id: $community_id,
                success: false,
            );

            throw $t;

        }

    }