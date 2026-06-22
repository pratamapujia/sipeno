<div id="sidebar">
  <div class="sidebar-wrapper active">
    <div class="sidebar-header position-relative">
      <div class="d-flex justify-content-between align-items-center">
        <div class="logo">
          <a href="/">SIPENO</a>
        </div>
        <div class="sidebar-toggler  x">
          <a href="javascript:void(0);" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
        </div>
      </div>
    </div>
    <div class="sidebar-menu">
      <ul class="menu">
        <li class="sidebar-title">Menu</li>
        <li class="sidebar-item  ">
          <a href="/" class='sidebar-link'>
            <i class="bi bi-grid-fill"></i>
            <span>Dashboard</span>
          </a>
        </li>
        <li class="sidebar-title">Data Master</li>
        <li class="sidebar-item  {{ request()->is('guru', 'guru/*') ? 'active' : '' }}">
          <a href="{{ route('guru.index') }}" class='sidebar-link'>
            <i class="bi bi-person-workspace"></i>
            <span>Data Guru</span>
          </a>
        </li>
        <li class="sidebar-item {{ request()->is('mapel', 'mapel/*') ? 'active' : '' }}">
          <a href="{{ route('mapel.index') }}" class='sidebar-link'>
            <i class="bi bi-journal-bookmark-fill"></i>
            <span>Mata Pelajaran</span>
          </a>
        </li>
        <li class="sidebar-item  ">
          <a href="/" class='sidebar-link'>
            <i class="bi bi-easel2-fill"></i>
            <span>Data Kelas</span>
          </a>
        </li>
        <li class="sidebar-item  ">
          <a href="/" class='sidebar-link'>
            <i class="bi bi-house-fill"></i>
            <span>Kelola Ruangan</span>
          </a>
        </li>
        <li class="sidebar-item  ">
          <a href="/" class='sidebar-link'>
            <i class="bi bi-calendar3-week"></i>
            <span>Tahun Ajaran</span>
          </a>
        </li>
        <li class="sidebar-title">Penjadwalan</li>
        <li class="sidebar-item  ">
          <a href="/" class='sidebar-link'>
            <i class="bi bi-calendar-heart"></i>
            <span>Plotting Mengajar</span>
          </a>
        </li>
        <li class="sidebar-item  ">
          <a href="/" class='sidebar-link'>
            <i class="bi bi-magic"></i>
            <span>Generate Jadwal</span>
          </a>
        </li>
        <li class="sidebar-item  ">
          <a href="/" class='sidebar-link'>
            <i class="bi bi-file-lock2"></i>
            <span>Atur Jadwal</span>
          </a>
        </li>
        <li class="sidebar-title">Laporan</li>
        <li class="sidebar-item  ">
          <a href="/" class='sidebar-link'>
            <i class="bi bi-calendar-week"></i>
            <span>Jadwal Saya</span>
          </a>
        </li>
        <li class="sidebar-item  ">
          <a href="/" class='sidebar-link'>
            <i class="bi bi-calendar3"></i>
            <span>Jadwal Guru</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</div>
