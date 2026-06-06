@extends('portal._layout', ['title' => 'New Ticket'])

@section('content')
<div class="container">
  <a href="{{ route('portal.tickets.index') }}" style="color:#4f46e5;font-size:13px;font-weight:600;margin-bottom:12px;display:inline-block">&larr; Kembali</a>
  <h1 style="font-size:24px;font-weight:800;margin-bottom:24px">New Support Ticket</h1>

  <div class="card">
    <form method="POST" action="{{ route('portal.tickets.store') }}">
      @csrf

      @if($errors->any())
        <div class="error">
          <ul style="margin:0;padding-left:16px">
            @foreach($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="field">
        <label class="label" for="subject">Subject</label>
        <input class="input" type="text" name="subject" id="subject" value="{{ old('subject') }}" required>
      </div>

      <div class="field">
        <label class="label" for="department_id">Department</label>
        <select class="input" name="department_id" id="department_id" required>
          <option value="">Pilih department</option>
          @foreach($departments as $d)
            <option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="field">
        <label class="label" for="priority_id">Priority</label>
        <select class="input" name="priority_id" id="priority_id" required>
          <option value="">Pilih priority</option>
          @foreach($priorities as $p)
            <option value="{{ $p->id }}" {{ old('priority_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="field">
        <label class="label" for="body">Description</label>
        <textarea class="input" name="body" id="body" rows="6" style="resize:vertical" required>{{ old('body') }}</textarea>
      </div>

      <button type="submit" class="btn btn-primary">Submit Ticket</button>
    </form>
  </div>
</div>
@endsection
