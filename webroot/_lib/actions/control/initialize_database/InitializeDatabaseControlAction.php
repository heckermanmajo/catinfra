<?php

    namespace _lib\actions\control\initialize_database;

    use _lib\core\Action;
    use _lib\core\DataBase;
    use _lib\core\DataClass;
    use _lib\model\ActionResultData;
    use _lib\model\AnalysisResultData;
    use _lib\model\Community;
    use _lib\model\EventLog;
    use _lib\model\HttpRequestData;
    use _lib\model\RawDataPage;
    use _lib\model\SentMail;
    use _lib\model\User;
    use _lib\model\UserCommunityRelation;
    use Throwable;

    final class InitializeDatabaseControlAction extends Action
    {

        protected function perform(): InitializeDatabaseControlActionResult
        {
            $db = DataBase::get_default_instance();

            $model_classes = [
                User::class,
                Community::class,
                UserCommunityRelation::class,
                EventLog::class,
                SentMail::class,
                HttpRequestData::class,
                RawDataPage::class,
                AnalysisResultData::class,
                ActionResultData::class,
            ];

            $errors = [];
            $success_count = 0;
            $skipped_count = 0;

            /** @var DataClass $class */
            foreach ($model_classes as $class)
            {
                $sql_statements = $class::create_and_alter_table($db);

                foreach ($sql_statements as $sql)
                {
                    try
                    {
                        $db->execute_string($sql);
                        $success_count++;
                    }
                    catch (Throwable $t)
                    {
                        // Silently ignore errors (columns might already exist)
                        if (str_contains($t->getMessage(), 'Duplicate column'))
                        {
                            $skipped_count++;
                        }
                        else
                        {
                            $errors[] = [
                                'class' => $class,
                                'sql' => $sql,
                                'error' => $t->getMessage()
                            ];
                        }
                    }
                }
            }

            $result = new InitializeDatabaseControlActionResult(success: true);
            $result->message = "Database initialized: $success_count SQL statements executed, $skipped_count skipped";
            $result->success_count = $success_count;
            $result->skipped_count = $skipped_count;
            $result->error_count = count($errors);
            $result->errors = $errors;
            $result->initialized_models = array_map(fn($c) => basename(str_replace('\\', '/', $c)), $model_classes);

            return $result;
        }

    }
