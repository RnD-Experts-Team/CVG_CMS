<h2>New contact form submission</h2>

<p><strong>Name:</strong> {{ $submission->full_name }}</p>
<p><strong>Email:</strong> {{ $submission->email }}</p>
<p><strong>Phone:</strong> {{ $submission->phone_number ?? '—' }}</p>
<p><strong>Project details:</strong></p>
<p>{{ $submission->project_details }}</p>

<hr>
<p style="color:#888;font-size:12px;">
    Submitted {{ $submission->created_at->format('M j, Y g:i A') }}
    from IP {{ $submission->ip_address ?? 'unknown' }}
</p>
