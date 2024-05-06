<x-mail::message>
    Dear {{ $appointment['name'] }},

    We are writing to confirm your appointment with {{ env('APP_NAME' )}} on {{ Carbon\Carbon::parse($appointment['booking_date_time'])->format('d/m/Y h:i A')}}.

    Appointment Details:
    Appointment Date Time: {{ Carbon\Carbon::parse($appointment['booking_date_time'])->format('d/m/Y h:i A')}}
    Appointment Status: {{ $appointment['status'] }}

    Please ensure you arrive on time for your appointment. If you need to reschedule or cancel, please let us know at least 24 hours in advance.

    If you have any questions or need further assistance, feel free to contact us.

    We look forward to seeing you soon!

    Warm regards,
    {{ env('APP_NAME') }}
</x-mail::message>