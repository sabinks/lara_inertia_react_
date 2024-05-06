<x-mail::message>
    Dear Admin,

    I hope this message finds you well.

    I wanted to inform you that we have received a message from a client through the "Contact Us" form on our website.
    The client, {{ $name }}, reached out regarding on "{{ $subject1 }}".

    Please find the details of the client's inquiry below:
    Name: {{ $name }}
    Email: {{ $email }}
    Contact Information: {{ $phone }}
    Subject: {{ $subject1 }}
    Message: {{ $message }}

    Kindly review the message and take appropriate action to address the client's inquiry in a timely manner.

    If you require any further assistance or clarification, please feel free to reach out to me.

    Thank you for your attention to this matter.

    Best regards,
    {{ config('app.name') }}
</x-mail::message>