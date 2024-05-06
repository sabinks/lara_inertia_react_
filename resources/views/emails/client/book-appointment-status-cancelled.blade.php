<x-mail::message>
    Dear {{ $appointment['name'] }},

    We regret to inform you that your appointment scheduled for {{ Carbon\Carbon::parse($appointment['booking_date_time'])->format('d/m/Y h:i A')}} has been cancelled.

    Appointment Details:
    Appointment Date Time: {{ Carbon\Carbon::parse($appointment['booking_date_time'])->format('d/m/Y h:i A')}}
    Appointment Status: {{ $appointment['status'] }}

    We apologize for any inconvenience this may cause.

    If you would like to reschedule your appointment or if you have any questions, please don't hesitate to contact us.

    Thank you for your understanding.

    Warm regards,
    {{ env('APP_NAME') }}
</x-mail::message>