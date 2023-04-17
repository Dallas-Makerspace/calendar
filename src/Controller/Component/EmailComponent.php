<?php

namespace App\Controller\Component;

use App\Model\Entity\Contact;
use App\Model\Entity\Event;
use App\Model\Entity\Registration;
use Cake\Controller\Component;
use Cake\I18n\Time;
use Cake\Mailer\Email;
use Throwable;

/** @noinspection PhpUnused */

class EmailComponent extends Component
{
    protected $_config = null;

    /**
     * Execute any other additional setup for your component.
     * @param array $config Extra config options to pass in.
     * @return void
     */
    public function initialize(array $config = [])
    {
        parent::initialize($config);
        $this->_config = $config;
    }

    /**
     * @param string $body the inner content body
     * @return string combined all-in-one message to email
     */
    private function generateContainer(string $body): string
    {
        $currentYear = date('Y');

        $pre = <<<PRE
            <!doctype html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>DMS Calendar</title>

                <style>
                    body {-webkit-font-smoothing: antialiased;-webkit-text-size-adjust: none;box-sizing: border-box;margin: 0;height: 100%;width: 100% !important;font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;font-size: 20px;line-height: 25px;background-color: white;color: #333;}  .content {margin-left: auto;margin-right: auto;max-width: 1000px;padding: 30px;}  .content .callout-center {text-align: center;width: 100%;}  .mb-20px {margin-bottom: 20px;}  .mt-20px {margin-top: 20px;}  .content .callout-center img {width: 50%;}  .bordered {background-color: #f6f6f6;padding: 20px;border-radius: 10px;}
                </style>
            </head>
            <body>
            <div class="content">
                <div class="callout-center mb-20px">
                    <img src="https://dallasmakerspace.org/wp-content/uploads/56_wide2.png" alt="Dallas Makerspace Logo"/>
                </div>
                <div class="bordered">
        PRE;

        $post = <<<POST
                </div>
                <div class="callout-center mt-20px">
                    &copy; $currentYear Dallas Makerspace
                </div>
            </div>
            </body>
            </html>
        POST;

        return $pre . $body . $post;
    }

    /**
     * @param Contact $contact The contact reference for the user who submitted the event.
     * @param Event $event The event reference
     * @return void
     */
    public function sendEventRejected(Contact $contact, Event $event)
    {
        $rejectionReason = ($event->rejection_reason ? $event->rejection_reason : 'No additional information given.');
        $subject = 'DMS Event Rejection: ' . $event->name;

        $message = <<<MSGBODY
            Hello $contact->name,<br/><br/>
            The following event you submitted to the Dallas Makerspace Calendar has been rejected.<br/><br/>
            <b>Event: </b>$event->name <br/>
            <b>Reason: </b>$rejectionReason <br/><br/>
            Regards,<br/>
            Dallas Makerspace Team
        MSGBODY;

        $this->sendEmail($contact->name, $contact->email, $subject, $this->generateContainer($message));
    }

    /**
     * @param Registration $registration Registration ref, contains user info to notify and the like.
     * @param Event $event Event ref
     * @return void
     */
    public function sendEventCancelled(Registration $registration, Event $event)
    {
        $subject = 'DMS Event Update: ' . $event->name . ' has been Cancelled';

        $message = <<<MSGBODY
            Hello $registration->name,<br/><br/>
            An event that you RSVP'd for has been cancelled.<br/><br/>
            <b>Event: </b>$event->name <br/><br/>
            If you paid to register for this event then a refund has been submitted for processing.<br/><br/>
            Regards,<br/>
            Dallas Makerspace Team
        MSGBODY;

        $this->sendEmail($registration->name, $registration->email, $subject, $this->generateContainer($message));
    }

    /**
     * @param Registration $registration Registration ref, contains user info to notify and the like.
     * @param Event $event Event ref
     * @return void
     */
    public function sendEventStarting(Registration $registration, Event $event)
    {
        $subject = 'DMS Reminder: ' . $event->name . ' Starts Soon';
        $time = new Time($event->event_start);
        $formattedTime = $time->i18nFormat('EEEE MMMM d, h:mma', 'America/Chicago');

        $message = <<<MSGBODY
            Hello $registration->name,<br/><br/>
            This is a reminder that you have an event starting soon at Dallas Makerspace.<br/><br/>
            <b>Event: </b>$event->name <br/>
            <b>Time: </b>$formattedTime <br/><br/>
            Full event details are available at <a href="https://calendar.dallasmakerspace.org/events/view/$event->id">https://calendar.dallasmakerspace.org/events/view/$event->id</a>.<br/><br/>
            Regards,<br/>
            Dallas Makerspace Team
        MSGBODY;

        $this->sendEmail($registration->name, $registration->email, $subject, $this->generateContainer($message));
    }

    /**
     * @param Registration $registration Registration ref, contains user info to notify and the like.
     * @param Event $event Event ref
     * @return void
     */
    public function sendUnapprovedRegistrationCancelled(Registration $registration, Event $event)
    {
        $subject = 'DMS Events: ' . $event->name . ' Registration Cancelled';

        $message = <<<MSGBODY
            Hello $registration->name,<br/><br/>
            Your registration for the following event was cancelled automatically by our system.<br/> <br/>
            <b>Event: </b>$event->name <br/> <br/>
            If you paid to register for this event then a refund has been submitted for processing.<br/><br/>
            Regards,<br/>
            Dallas Makerspace Team
        MSGBODY;

        $this->sendEmail($registration->name, $registration->email, $subject, $this->generateContainer($message));
    }

    /**
     * @param Registration $registration Registration ref, contains user info to notify and the like.
     * @param Event $event Event ref
     * @return void
     */
    public function sendCancellationReminder(Registration $registration, Event $event)
    {
        $subject = 'DMS Reminder: ' . $event->name . ' Cancellation Cutoff is Soon';
        $time = new Time($event->event_start);
        $formattedTime = $time->i18nFormat('EEEE MMMM d, h:mma', 'America/Chicago');

        $message = <<<MSGBODY
            Hello $registration->name,<br/><br/>
            This is a reminder that you don't have much time left to cancel your RSVP for the
            following event. If you are still planning on attending you can ignore this reminder.<br/><br/>
            <b>Event: </b>$event->name <br/>
            <b>Time: </b>$formattedTime <br/>
            <br/>
            If you need to review or cancel your RSVP you can do so <a
                href="https://calendar.dallasmakerspace.org/registrations/view/$registration->id?edit_key=$registration->edit_key">here</a>.
            <br/><br/>
            Regards,<br/>
            Dallas Makerspace Team
            <br><br><br>
            <small>
                If the above link doesn't work, try copying and pasting this into your browser:
                https://calendar.dallasmakerspace.org/registrations/view/$registration->id?edit_key=$registration->edit_key
            </small>
        MSGBODY;

        $this->sendEmail($registration->name, $registration->email, $subject, $this->generateContainer($message));
    }

    /**
     * @param Registration $registration Registration ref, contains user info to notify and the like.
     * @param Event $event Event ref
     * @return void
     */
    public function sendRegistrationConfirmation(Registration $registration, Event $event)
    {
        $subject = 'DMS Event Confirmation: ' . $event->name;
        $time = new Time($event->event_start);
        $formattedTime = $time->i18nFormat('EEEE MMMM d, h:mma', 'America/Chicago');
        $currentYear = date('Y');

        $message = <<<MSGBODY
            Hello $registration->name,<br/><br/>
            You're confirmed for an event! Keep this email for your records.<br/>
            <br/>
            <b>Event: </b>$event->name <br/>
            <b>Time: </b>$formattedTime <br/>
            <br/>
            If you need to review or cancel your RSVP you can do so <a
                href="https://calendar.dallasmakerspace.org/registrations/view/$registration->id?edit_key=$registration->edit_key">here</a>.
            <br/><br/>
            Regards,<br/>
            Dallas Makerspace Team
            <br><br><br>
            <small>
                If the above link doesn't work, try copying and pasting this into your browser:
                https://calendar.dallasmakerspace.org/registrations/view/$registration->id?edit_key=$registration->edit_key
            </small>
        MSGBODY;

        $this->sendEmail($registration->name, $registration->email, $subject, $this->generateContainer($message));
    }

    /**
     * @param Registration $registration Registration ref, contains user info to notify and the like.
     * @param Event $event Event ref
     * @return void
     */
    public function sendRegistrationPending(Registration $registration, Event $event)
    {
        $subject = 'DMS Event Pending: ' . $event->name;
        $time = new Time($event->event_start);
        $formattedTime = $time->i18nFormat('EEEE MMMM d, h:mma', 'America/Chicago');

        $message = <<<MSGBODY
            Hello $registration->name,<br/><br/>
            Your registration has been submitted for an event. The event host will need to accept
            your RSVP before you will be confirmed for the event. A follow-up notification will be
            sent once your registration is approved or rejected.<br/><br/>
            <b>Event: </b>$event->name <br/>
            <b>Time: </b>$formattedTime <br/>
            <br/>
            If you need to review or cancel your RSVP you can do so <a
                href="https://calendar.dallasmakerspace.org/registrations/view/$registration->id?edit_key=$registration->edit_key">here</a>.
            <br/><br/>
            Regards,<br/>
            Dallas Makerspace Team
            <br><br><br>
            <small>
                If the above link doesn't work, try copying and pasting this into your browser:
                https://calendar.dallasmakerspace.org/registrations/view/$registration->id?edit_key=$registration->edit_key
            </small>
        MSGBODY;

        $this->sendEmail($registration->name, $registration->email, $subject, $this->generateContainer($message));
    }

    /**
     * @param Registration $registration Registration ref, contains user info to notify and the like.
     * @param Event $event Event ref
     * @return void
     */
    public function sendRegistrationRequested(Registration $registration, Event $event)
    {
        $subject = 'DMS Attendance Request: ' . $event->name;
        $time = new Time($event->event_start);
        $formattedTime = $time->i18nFormat('EEEE MMMM d, h:mma', 'America/Chicago');
        $userName = $event->contact->name;

        $message = <<<MSGBODY
            Hello $userName,<br/>
            <br/>
            Someone has requested to attend your event.<br/>
            <br/>
            <b>Event: </b>$event->name<br/>
            <b>Time: </b>$formattedTime<br/><br/>
            <b>Attendee: </b>$registration->name <br/>
            <b>Attendee Email: </b><a href="mailto:$registration->email">$registration->email</a><br/>
            <br/>
            Approve or deny this request at your earliest convenience at <a
                href="https://calendar.dallasmakerspace.org/events/view/$event->id">https://calendar.dallasmakerspace.org/events/view/$event->id</a>
            <br/><br/>
            Regards,<br/>
            Dallas Makerspace Team
        MSGBODY;

        $this->sendEmail($event->contact->name, $event->contact->email, $subject, $this->generateContainer($message));
    }

    /**
     * @param Registration $registration Registration ref, contains user info to notify and the like.
     * @param Event $event Event ref
     * @return void
     */
    public function sendRegistrationApproved(Registration $registration, Event $event)
    {
        $subject = 'DMS Event Update: Attendance approved for ' . $event->name;
        $time = new Time($event->event_start);
        $formattedTime = $time->i18nFormat('EEEE MMMM d, h:mma', 'America/Chicago');

        $message = <<<MSGBODY
            Hello $registration->name,<br/>
            <br>
            You've been approved to attend an event! Keep this email for your records.<br/>
            <br/>
            <b>Event: </b>$event->name <br/>
            <b>Time: </b>$formattedTime <br/>
            <br/>
            If you need to review or cancel your RSVP you can do so <a
                href="https://calendar.dallasmakerspace.org/registrations/view/$registration->id?edit_key=$registration->edit_key">here</a>.
            <br/><br/>
            Regards,<br/>
            Dallas Makerspace Team
            <br><br><br>
            <small>
                If the above link doesn't work, try copying and pasting this into your browser:
                https://calendar.dallasmakerspace.org/registrations/view/$registration->id?edit_key=$registration->edit_key
            </small>
        MSGBODY;

        $this->sendEmail($registration->name, $registration->email, $subject, $this->generateContainer($message));
    }

    /**
     * @param Registration $registration Registration ref, contains user info to notify and the like.
     * @param Event $event Event ref
     * @return void
     */
    public function sendRegistrationRejected(Registration $registration, Event $event)
    {
        $subject = 'DMS Event Update: Attendance Rejected for ' . $event->name;

        $message = <<<MSGBODY
            Hello $registration->name,<br/>
            <br/>
            Your RSVP for the following event has been cancelled due to the event organizer
            rejecting your registration.<br/>
            <br/>
            <b>Event: </b>$event->name<br/>
            <br/>
            If you paid to register for this event then a refund has been submitted for
            processing.
            <br/><br/>
            Regards,<br/>
            Dallas Makerspace Team
        MSGBODY;

        $this->sendEmail($registration->name, $registration->email, $subject, $this->generateContainer($message));
    }

    /**
     * @param Registration $registration Registration ref, contains user info to notify and the like.
     * @param Event $event Event ref
     * @return void
     */
    public function sendRegistrationCancelled(Registration $registration, Event $event)
    {
        $subject = 'DMS Event Cancellation: ' . $event->name;

        $message = <<<MSGBODY
            Hello $registration->name,<br/>
            <br/>
            Your RSVP for the following event has been cancelled.<br/>
            <br/>
            <b>Event: </b>$event->name <br/>
            <br/>
            If you paid to register for this event then a refund has been submitted
            for processing.
            <br/><br/>
            Regards,<br/>
            Dallas Makerspace Team
        MSGBODY;

        $this->sendEmail($registration->name, $registration->email, $subject, $this->generateContainer($message));
    }

    /**
     * @param string $subject The subject line text
     * @param int $length Mac length
     * @return string
     * @noinspection PhpSameParameterValueInspection
     */
    private function limitSubject(string $subject = "", int $length = 60): string
    {
        return strlen($subject) > $length ? substr($subject, 0, $length - 3) . "..." : $subject;
    }

    /**
     * @param string $name Name of the person
     * @param string $addr Email address of the person
     * @param string $subject Email subject
     * @param string $body Email body
     * @return void
     */
    private function sendEmail(string $name, string $addr, string $subject, string $body)
    {
        try {
            $email = new Email();
            $email
                ->setTransport('default')
                ->setFrom(['admin@dallasmakerspace.org' => 'Dallas Makerspace'])
                ->setTo([$addr => $name])
                ->setSubject($this->limitSubject($subject))
                ->setEmailFormat('html')
                ->send($body);
        } catch (Throwable $th) {
            $this->log($th);
        }
    }
}
