<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Mailer\Email;
use App\Model\Entity\Event;
use App\Model\Entity\Contact;
use App\Model\Entity\Registration;

class EmailComponent extends Component
{
    protected $_config = null;
    // Execute any other additional setup for your component.
    public function initialize(array $config)
    {
        parent::initialize();
        $this->_config = $config;
    }

    public function sendEventRejected(
        Contact $contact = null,
        Event $event = null
    ){
        $rejection_reason = ($event->rejection_reason ? $event->rejection_reason : 'No additional information given.');
        $subject = 'DMS Event Rejection: ' . $event->name;
        $message =  <<<MAIL
        $contact->name , <br/><br/>
        The following even you submitted to the Dallas Makerspace Calendar has been rejected.<br/><br/>
        $event->name <br/><br/>
        Reason: $rejection_reason <br/><br/>
        Dallas Makerspace
        MAIL;

        $this->sendEmail(
            $contact->name,
            $contact->email,
            $subject,
            $message
        );
    }

    public function sendEventCancelled(
        Registration $registration,
        Event $event
    ){
        $subject = 'DMS Event Update: ' . $event->name . ' has been Cancelled';
        $message =  <<<MAIL
        $registration->name , <br/><br/>
        An event that you RSVP'd for has been cancelled.<br/><br/
        $event->name <br/><br/>
        If you paid to register for this event then a refund has been submitted for processing.<br/><br/>
        Dallas Makerspace
        MAIL;

        sendEmail(
            $registration->name,
            $registration->email,
            $subject,
            $message
        );
    }

    public function sendEventStarting(
        Registration $registration,
        Event $event
    ){
        $subject = 'DMS Reminder: ' . $event->name . ' Starts Soon';
        $message =  <<<MAIL
        $registration->name ,<br/><br/>
        This is a reminder that you have an event starting soon at Dallas Makerspace.<br/><br/>
        $event->name <br/>
        $formattedTime <br/><br/>
        Full event details are available at https://calendar.dallasmakerspace.org/events/view/$event->id
        <br/><br/>
        Dallas Makerspace
        MAIL;

        sendEmail(
            $registration->name,
            $registration->email,
            $subject,
            $message
        );
    }

    public function sendUnapprovedRegistrationCancelled(
        Registration $registration,
        Event $event
    ){
        $subject = 'DMS Events: ' . $event->name . ' Registration Cancelled';
        $message = <<<MAIL
            $registration->name , <br/><br/>
            Your registration for the following event was cancelled automatically by our system.<br/>
            <br/>
            $event->name <br/>
            <br/>
            If you paid to register for this event then a refund has been submitted for processing.<br/>
            <br/>
            Dallas Makerspace
        MAIL;

        sendEmail(
            $registration->name,
            $registration->email,
            $subject,
            $message
        );
    }

    public function sendCancellationReminder(
        Registration $registration,
        Event $event
    ){
        $subject ='DMS Reminder: ' . $event->name . ' Cancellation Cutoff is Soon';
        $message = <<<MAIL
            $registration->name , <br/><br/>
            This is a reminder that you don't have much time left to cancel your RSVP for the
            following event. If you are still planning on attending you can ignore this reminder.<br/>
            <br/>
            $event->name <br/>
            <br/>
            If you need to review or cancel your RSVP you can
            do so at https://calendar.dallasmakerspace.org/registrations/view/$registration->id?edit_key=$registration->edit_key <br/>
            <br/>
            Dallas Makerspace
        MAIL;

        sendEmail(
            $registration->name,
            $registration->email,
            $subject,
            $message
        );
    }

    public function sendRegistrationConfirmation(
        Registration $registration,
        Event $event
    ){
        $subject ='DMS Event Confirmation: ' . $event->name;
        $message = <<<MAIL
            $registration->name , <br/><br/>
            You're confirmed for an event! Keep this email for your records.<br/>
            <br/>
            $eventReference->name <br/>
            $formattedTime <br/>
            <br/>
            If you need to review or cancel your RSVP you can do so at
            https://calendar.dallasmakerspace.org/registrations/view/$event->id?edit_key=$event->edit_key <br/>
            <br/>
            Dallas Makerspace
        MAIL;

        sendEmail(
            $registration->name,
            $registration->email,
            $subject,
            $message
        );
    }

    public function sendRegistrationPending(
        Registration $registration,
        Event $event
    ){
        $subject ='DMS Event Pending: ' . $event->name;
        $time = new Time($event->event_start);
        $formattedTime = $time->i18nFormat('EEEE MMMM d, h:mma', 'America/Chicago');
        $message = <<<MAIL
            $registration->name , <br/><br/>
            Your registration has been submitted for an event. The event host will need to accept
            your RSVP before you will be confirmed for the event. A follow up notification will be
            sent once your registration is approved or rejected.<br/>
            <br/>
            $event->name <br/>
            $formattedTime <br/>
            <br/>
            If you need to review or cancel your RSVP you can do so at
            https://calendar.dallasmakerspace.org/registrations/view/$registration->id?edit_key=$registration->edit_key <br/>
            <br/>
            Dallas Makerspace
        MAIL;

        sendEmail(
            $registration->name,
            $registration->email,
            $subject,
            $message
        );
    }

    public function sendRegistrationRequested(
        Registration $registration,
        Event $event
    ){
        $subject ='DMS Attendance Request: ' . $event->name;
        $time = new Time($event->event_start);
        $formattedTime = $time->i18nFormat('EEEE MMMM d, h:mma', 'America/Chicago');


        $message = <<<MAIL
            $contact->name ,<br/>
            <br/>
            Someone new has requested to attend $event->name <br/>
            <br/>
            $registration->name <br/>
            <a href="mailto:$registration->email">$registration->email </a><br/>
            <br/>
            Approve or deny this request at your earliest convenience at
            https://calendar.dallasmakerspace.org/events/view/$eventReference->id
            <br/><br/>
            Dallas Makerspace
        MAIL;

        sendEmail(
            $event->contact->name,
            $event->contact->email,
            $subject,
            $message
        );
    }

    public function sendRegistrationApproved(
        Registration $registration,
        Event $event
    ){
        $subject ='DMS Event Update: Attendance approved for ' . $event->name;
        $message = <<<MAIL
            $registration->name ,<br/>
            You've been approved to attend an event! Keep this email for your records.<br/>
            <br/>
            $event->name <br/>>
            $formattedTime <br/>
            <br/>
            If you need to review or cancel your RSVP you can do so at
            https://calendar.dallasmakerspace.org/registrations/view/$registration->id?edit_key=$registration->edit_key
            <br/><br/>
            Dallas Makerspace
        MAIL;

        sendEmail(
            $registration->name,
            $registration->email,
            $subject,
            $message
        );
    }

    public function sendRegistrationRejected(
        Registration $registration,
        Event $event
    ){
        $subject ='DMS Event Update: Attendance Rejected for ' . $event->name;
        $message = <<<MAIL
            $registration->name ,<br/>
            Your RSVP for the following event has been cancelled due to the event organizer
            rejecting your registration.<br/>
            <br/>
            $event->name <br/>
            <br/>
            If you paid to register for this event then a refund has been submitted for
            processing.<br/>
            <br/>
            Dallas Makerspace";

        MAIL;

        sendEmail(
            $registration->name,
            $registration->email,
            $subject,
            $message
        );
    }

    public function sendRegistrationCancelled(
        Registration $registration,
        Event $event
    ){
        $subject ='DMS Event Cancellation: ' . $event->name;
        $message = <<<MAIL
            $contact->name ,<br/>
            <br/>
            Your RSVP for the following event has been cancelled.<br/>
            <br/>
            $event->name <br/>
            <br/>
            If you paid to register for this event then a refund has been submitted
            for processing.
            <br/>
            <br/>
            Dallas Makerspace
        MAIL;

        sendEmail(
            $registration->name,
            $registration->email,
            $subject,
            $message
        );
    }

    private function limitSubject(
        string $subject = "",
        int $length = 60
    ) : string {
        return strlen($subject) > $length ? substr($subject, 0, $length-3) . "..." : $subject;
    }

    private function sendEmail(
        string $name = null,
        string $addr = null,
        string $subject = null,
        string $body = null
    ) {
        try {

            $email = new Email();
            $email
                ->transport('default')
                ->from(['admin@dallasmakerspace.org' => 'Dallas Makerspace'])
                ->to([$addr => $name])
                ->subject($this->limitSubject($subject))
                ->emailFormat('html')
                ->send($body);
        } catch (\Throwable $th) {
            $this->log($th);
        }
    }
}
?>
