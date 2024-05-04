<x-mail::message>
    Dear {{ $bookAppointment['name'] }},

    I trust this email reaches you in good spirits. I wanted to express my gratitude for booking an appointment with us. We are eagerly anticipating our meeting.

    Here are the details of our scheduled appointment:

    Appointment Date Time: {{ Carbon\Carbon::parse($bookAppointment['booking_date_time'])->format('d/m/Y h:i A')}}

    Please don't hesitate to reach out if you have any questions or need to reschedule.

    We value your time and are committed to making this appointment as productive as possible.

    Looking forward to our discussion.

    Warm regards,
    {{ env('APP_NAME') }}
</x-mail::message>