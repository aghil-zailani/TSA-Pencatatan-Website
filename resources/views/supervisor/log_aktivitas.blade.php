@extends('layouts/main')

@section('container')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>{{ $judul }}</title>
  <style>
    body { font-family: 'Poppins', sans-serif; color: #333; }
    h3, h4 { font-family: 'Poppins', sans-serif; font-weight: 700; color: #444; }
    .card { border-radius: 0.75rem; box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.05); }
    .card-header { background-color: #f8f9fa; border-bottom: 1px solid #e9ecef; border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem; padding: 1.5rem; }
    .card-title { font-weight: 600; color: #343a40; }
    .table { --bs-table-bg: #fff; }
    .table thead th { font-family: 'Poppins', sans-serif; font-weight: 600; background-color: #e9ecef; color: #495057; text-align: center; }
    .table tbody td { font-family: 'Poppins', sans-serif; color: #495057; vertical-align: middle; }
  </style>
</head>

<body>
  <div id="app">
    <div id="main">
      <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
          <i class="bi bi-justify fs-3"></i>
        </a>
      </header>

      <div class="page-heading">
        <h3>{{ $judul }}</h3>
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ $judul }}</li>
        </ol>
      </nav>

      <div class="page-content">
        <section class="section">
          <div class="card shadow h-md-50">
            <div class="card-header">
              <h4 class="card-title">Log Aktivitas Seluruh Staff</h4>
            </div>
            <div class="card-body">
              <form method="GET" class="row g-3 mb-3">
                <div class="col-md-2">
                  <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                  <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2">
                  <button class="btn btn-success btn-sm">Filter</button>
                  <a href="{{ route('supervisor.log.aktivitas') }}" class="btn btn-secondary btn-sm">Reset</a>
                </div>
              </form>

              <div class="table-responsive">
                <table id="dataTable" class="table table-striped table-bordered table-hover" width="100%">
                  <thead>
                    <tr class="text-center">
                      <th>No</th>
                      <th>User</th>
                      <th>Role</th>
                      <th>Page</th>
                      <th>Action</th>
                      <th>Feature</th>
                      <th>Deskripsi</th>
                      <th>Waktu</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($logs as $index => $log)
                      <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $log->user->username ?? '-' }}</td>
                        @if( $log->user->role == 'staff_gudang' ) 
                            <td>Staff Gudang</td>
                        @elseif( $log->user->role == 'supervisor' ) 
                            <td>Supervisor</td>
                        @endif
                        <td>{{ $log->page_accessed }}</td>
                        <td>{{ $log->action }}</td>
                        <td>{{ $log->feature_used }}</td>
                        <td>{{ $log->description }}</td>
                        <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                      </tr>
                    @empty
                      <tr><td colspan="8" class="text-center text-muted">Tidak ada aktivitas.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>

            </div>
          </div>
        </section>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#dataTable').DataTable({
        paging: true,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
        pageLength: 10,
        ordering: true,
        searching: true,
        info: true,
        responsive: true
      });
    });
  </script>
</body>
</html>
@endsection
