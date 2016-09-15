<div class="form-group{{ $errors->has('nombre') ? ' has-error' : '' }}">
    {!! Form::label('nombre', 'Nombre') !!}
    {!! Form::text('nombre', null, ['class' => 'form-control', 'required' => '']) !!}
    <small class="text-danger">{{ $errors->first('nombre') }}</small>
</div>

<div class="form-group{{ $errors->has('rfc') ? ' has-error' : '' }}">
    {!! Form::label('rfc', 'RFC') !!}
    {!! Form::text('rfc', null, ['class' => 'form-control', 'required' => '']) !!}
    <small class="text-danger">{{ $errors->first('rfc') }}</small>
</div>

<button type="submit" class="btn btn-success">{{$submitButtonText}}</button>