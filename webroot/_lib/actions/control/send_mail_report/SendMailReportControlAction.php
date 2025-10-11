<?php

    namespace _lib\actions\control\send_mail_report;


    use _lib\core\Action;
    use _lib\core\App;
    use _lib\model\Community;
    use _lib\model\User;

    final class SendMailReportControlAction extends Action
    {
        function __construct(
            public User $target_user,
            public Community $target_community,
        ) {
            $app = App::get_instance();
        }

        protected function perform(): SendMailReportControlActionResult
        {
            return new SendMailReportControlActionResult();
        }

    }