<?php

    $m = [];

    # one user represents a skool account OR an admin
    $U = "User";
    $m[] = lib::table_creation_sql($U);
    $m[] = lib::string_column_sql($U, "username");
    $m[] = lib::string_column_sql($U, "password_hash");
    $m[] = lib::string_column_sql($U, "email");
    $m[] = lib::string_column_sql($U, "status");
    $m[] = lib::string_column_sql($U, "skool_user_id");
    $m[] = lib::integer_column_sql($U, "is_admin");
    $m[] = lib::string_column_sql($U, "SKOOL_AUTH_TOKEN");
    $m[] = lib::string_column_sql($U, "SKOOL_CLIENT_ID");
    $m[] = lib::string_column_sql($U, "SKOOL_GA");
    $m[] = lib::string_column_sql($U, "SKOOL_GA_B9");
    $m[] = lib::string_column_sql($U, "SKOOL_GA_D0XK");
    $m[] = lib::string_column_sql($U, "SKOOL_GCL_AU");
    $m[] = lib::string_column_sql($U, "SKOOL_FBP");
    $m[] = lib::string_column_sql($U, "SKOOL_AJS_ANON");
    $m[] = lib::string_column_sql($U, "SKOOL_WAF_COOKIE");
    $m[] = lib::string_column_sql($U, "SKOOL_WAF_HEADER");

    # one community represents one skool community
    $C = "Community";
    $m[] = lib::table_creation_sql($C);
    $m[] = lib::string_column_sql($C, "tenant_slug");
    $m[] = lib::string_column_sql($C, "tenant_name");
    $m[] = lib::string_column_sql($C, "skool_id");
    $m[] = lib::string_column_sql($C, "primary_community");
    $m[] = lib::string_column_sql($C, "created_by_user_id");
    $m[] = lib::string_column_sql($C, "created_by_user_name");

    $UCR = "UserCommunityRelation";
    $m[] = lib::table_creation_sql($UCR);
    $m[] = lib::integer_column_sql($UCR, "community_id");
    $m[] = lib::integer_column_sql($UCR, "user_id");
    $m[] = lib::string_column_sql($UCR, "relation_type");

    $RDP = "RawDataPage";
    $m[] = lib::table_creation_sql($RDP);
    $m[] = lib::string_column_sql($RDP, "page_type");
    $m[] = lib::string_column_sql($RDP, "related_skoolid"); # if this is a chat, of a user profile, etc.
    $m[] = lib::long_string_column_sql($RDP, "content");
    $m[] = lib::string_column_sql($RDP, "logs");
    $m[] = lib::string_column_sql($RDP, "trace");
    $m[] = lib::string_column_sql($RDP, "community_id");
    $m[] = lib::integer_column_sql($RDP, "success");

    $ARD = "AnalysisResultData";
    $m[] = lib::table_creation_sql($ARD);
    $m[] = lib::string_column_sql($ARD, "result_type");
    $m[] = lib::string_column_sql($ARD, "source_file_name");
    $m[] = lib::string_column_sql($ARD, "content");
    $m[] = lib::string_column_sql($ARD, "logs");
    $m[] = lib::string_column_sql($ARD, "trace");
    $m[] = lib::string_column_sql($RDP, "community_id");
    $m[] = lib::integer_column_sql($ARD, "success");

    $EL = "EventLog";
    $m[] = lib::table_creation_sql($EL);
    $m[] = lib::integer_column_sql($EL, "user_id");
    $m[] = lib::integer_column_sql($EL, "community_id");
    $m[] = lib::string_column_sql($EL, "event_type");
    $m[] = lib::string_column_sql($EL, "priority");
    $m[] = lib::integer_column_sql($EL, "done");
    $m[] = lib::string_column_sql($EL, "event_description");
    $m[] = lib::string_column_sql($EL, "event_data");
    $m[] = lib::string_column_sql($EL, "trace");

    $SM = "SentMail";
    $m[] = lib::table_creation_sql($SM);
    $m[] = lib::integer_column_sql($SM, "user_id");
    $m[] = lib::integer_column_sql($SM, "community_id");
    $m[] = lib::string_column_sql($SM, "subject");
    $m[] = lib::string_column_sql($SM, "body");
    $m[] = lib::string_column_sql($SM, "warnings");
    $m[] = lib::string_column_sql($SM, "trace");
    $m[] = lib::integer_column_sql($ARD, "success");

    return $m;