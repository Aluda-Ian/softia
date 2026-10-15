New message from the Softia website

Topic:   {{ $contactMessage->topicLabel() }}
Name:    {{ $contactMessage->name }}
Email:   {{ $contactMessage->email }}
Phone:   {{ $contactMessage->phone ?: '-' }}
Company: {{ $contactMessage->company ?: '-' }}

{{ $contactMessage->message }}

---
Reply to this email to answer {{ $contactMessage->name }} directly.
View all messages: {{ url('/admin/messages') }}
