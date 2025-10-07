<?php

    function transform_test(int $community_id): array {

        $data = lib::select(
            "SELECT * FROM `RawDataPage` WHERE community_id = :community_id and type = :about",
            [":community_id" => $community_id]
        );

        # get some data from it and return it
        $about = $data["pageProps"]["currentGroup"]["metadata"]["lpDescription"];

        return [
            "about" => $about,
        ];

    }