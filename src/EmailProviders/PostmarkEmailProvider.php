<?php

/*
 *	Copyright 2015 RhubarbPHP
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace Rhubarb\PostmarkEmail\EmailProviders;

use Postmark\Models\PostmarkAttachment;
use Postmark\Models\PostmarkException;
use Postmark\PostmarkClient;
use Rhubarb\Crown\Exceptions\EmailException;
use Rhubarb\Crown\Exceptions\SettingMissingException;
use Rhubarb\Crown\Sendables\Email\Email;
use Rhubarb\Crown\Sendables\Email\EmailProvider;
use Rhubarb\Crown\Sendables\Sendable;
use Rhubarb\PostmarkEmail\Settings\PostmarkSettings;

class PostmarkEmailProvider extends EmailProvider
{
    /**
     * Sends the sendable.
     *
     * Implemented by the concrete provider type.
     *
     * @param Sendable $email
     * @return mixed
     * @throws EmailException
     * @throws SettingMissingException
     */
    public function send(Sendable $email)
    {
        /**
         * @var Email $email
         */
        $settings = PostmarkSettings::singleton();
        $token = $settings->serverToken;

        if ($token === null) {
            throw new SettingMissingException(PostmarkSettings::class, "ServerToken");
        }

        $postMarkAttachments = [];
        $emailAttachments = $email->getAttachments();

        foreach ($emailAttachments as $emailAttachment) {
            $postMarkAttachment = PostmarkAttachment::fromFile($emailAttachment->path, $emailAttachment->name);
            $postMarkAttachments[] = $postMarkAttachment;
        }

        try {
            $client = new PostmarkClient($token);
            $client->sendEmail(
                (string)$email->getSender(),
                $email->getRecipientList(),
                $email->getSubject(),
                $email->getHtml(),
                $email->getText(),
                null,
                false,
                (string)$email->getSender(),
                null,
                null,
                null,
                $postMarkAttachments
            );
        } catch (PostmarkException $er) {
            throw new EmailException($er->getMessage(), $er);
        }
    }
}