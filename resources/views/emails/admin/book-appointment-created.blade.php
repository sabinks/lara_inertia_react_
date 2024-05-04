<x-mail::message>
    Dear Admin,

    I hope this email finds you well. I wanted to inform you that client have booked an appointment. Please have a look.

    Here are the details:
    Client Name: {{ $bookAppointment['name'] }}
    Email: {{ $bookAppointment['email']}}
    Phone: {{ $bookAppointment['phone']}}
    Date of Birth: {{ Carbon\Carbon::parse($bookAppointment['dob'])->format('d/m/Y')}}
    Appointment Date Time: {{ Carbon\Carbon::parse($bookAppointment['booking_date_time'])->format('d/m/Y h:i A')}}
    Description: {{ $bookAppointment['description']}}

    Please ensure that the necessary arrangements are made to accommodate this appointment.

    Thank you for your attention to this matter.

    Best regards,
    {{env('APP_NAME')}}

</x-mail::message>