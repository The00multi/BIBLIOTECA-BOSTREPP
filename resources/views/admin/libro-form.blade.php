@extends('layouts.admin')

@section('titulo', $libro ? 'Editar libro' : 'Nuevo libro')

@section('contenido')
@php
    $categorias = $categorias ?? [];
@endphp

<form action="{{ $libro ? route('admin.libros.actualizar', $libro['id']) : route('admin.libros.guardar') }}" method="POST" enctype="multipart/form-data" class="card admin-form-card border-0 shadow-sm">
    @csrf
    @if ($libro) @method('PUT') @endif
    <div class="card-body p-4">
        <div class="row g-4">

            <div class="col-lg-8">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="titulo" class="form-label">Título</label>
                        <input type="text" name="title" id="titulo" class="form-control" maxlength="255" value="{{ old('title', $libro['titulo'] ?? '') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="autor" class="form-label">Autor</label>
                        <input type="text" name="author" id="autor" class="form-control" value="{{ old('author', $libro['autor'] ?? '') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="editorial" class="form-label">Editorial</label>
                        <input type="text" name="publisher" id="editorial" class="form-control" value="{{ old('publisher', $libro['editorial'] ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="isbn" class="form-label">ISBN</label>
                        <input type="text" name="isbn" id="isbn" class="form-control" maxlength="20" value="{{ old('isbn', $libro['isbn'] ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="anio" class="form-label">Año de publicación</label>
                        <input type="number" name="publication_year" id="anio" class="form-control" min="1000" max="{{ date('Y') + 1 }}" value="{{ old('publication_year', $libro['anio'] ?? '') }}">
                    </div>

                    <div class="col-12">
                        <span class="form-label d-block">Categorías</span>
                        @foreach ($categorias as $i => $categoria)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $categoria }}" id="cat{{ $i }}" {{ in_array($categoria, old('categories', $libro['categorias'] ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="cat{{ $i }}">{{ $categoria }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <label for="portada" class="form-label">Portada</label>
                <img id="vista-previa" src="{{ !empty($libro['portada']) ? asset('storage/'.$libro['portada']) : asset('images/portada-sin-portada.png') }}" class="portada rounded border mb-2" alt="Vista previa de la portada del libro">
                <input type="file" name="cover" id="portada" class="form-control" accept="image/png, image/jpeg, image/webp">
                <div class="form-text">JPG, PNG o WEBP, máximo 2 MB.</div>
                <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="is_new" value="1" id="novedad" @checked(old('is_new', $libro['novedad'] ?? false))><label class="form-check-label" for="novedad">Marcar como novedad</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="is_featured" value="1" id="destacado" @checked(old('is_featured', $libro['popular'] ?? false))><label class="form-check-label" for="destacado">Marcar como destacado</label></div>
            </div>

        </div>
    </div>
    <div class="card-footer admin-form-footer border-0 p-4 pt-0 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Guardar libro</button>
        <a href="{{ route('admin.libros') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // Vista previa de la portada antes de guardar
    document.getElementById('portada').addEventListener('change', e => {
        const archivo = e.target.files[0];
        if (!archivo) return;
        if (archivo.size > 2 * 1024 * 1024) {
            alert('La imagen supera los 2 MB. Elige una más liviana.');
            e.target.value = '';
            return;
        }
        document.getElementById('vista-previa').src = URL.createObjectURL(archivo);
    });
</script>
@endpush
