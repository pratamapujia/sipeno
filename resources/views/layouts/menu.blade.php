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
        {{-- Menu Untuk Admin --}}
        @role('admin')
          <li class="sidebar-item {{ request()->is('admin') ? 'active' : '' }} ">
            <a href="{{ route('admin.dashboard') }}" class='sidebar-link'>
              <i class="bi bi-grid-fill"></i>
              <span>Dashboard</span>
            </a>
          </li>
          <li class="sidebar-title">Data Master</li>
          <li class="sidebar-item  {{ request()->is('admin/m/guru', 'admin/m/guru/*') ? 'active' : '' }}">
            <a href="{{ route('admin.m.guru.index') }}" class='sidebar-link'>
              <i class="bi bi-person-workspace"></i>
              <span>Data Guru</span>
            </a>
          </li>
          <li class="sidebar-item {{ request()->is('admin/m/mapel', 'admin/m/mapel/*') ? 'active' : '' }}">
            <a href="{{ route('admin.m.mapel.index') }}" class='sidebar-link'>
              <i class="bi bi-journal-bookmark-fill"></i>
              <span>Mata Pelajaran</span>
            </a>
          </li>
          <li class="sidebar-item {{ request()->is('admin/m/kelas', 'admin/m/kelas/*') ? 'active' : '' }}">
            <a href="{{ route('admin.m.kelas.index') }}" class='sidebar-link'>
              <i class="bi bi-easel2-fill"></i>
              <span>Data Kelas</span>
            </a>
          </li>
          {{-- <li class="sidebar-item {{ request()->is('admin/m/ruangan', 'admin/m/ruangan/*') ? 'active' : '' }}">
            <a href="{{ route('admin.m.ruangan.index') }}" class='sidebar-link'>
              <i class="bi bi-house-fill"></i>
              <span>Kelola Ruangan</span>
            </a>
          </li> --}}
          <li class="sidebar-item {{ request()->is('admin/m/slotJam', 'admin/m/slotJam/*') ? 'active' : '' }}">
            <a href="{{ route('admin.m.slotJam.index') }}" class='sidebar-link'>
              <i class="bi bi-clock"></i>
              <span>Slot Jam</span>
            </a>
          </li>
          <li class="sidebar-item {{ request()->is('admin/m/thnAjaran', 'admin/m/thnAjaran/*') ? 'active' : '' }} ">
            <a href="{{ route('admin.m.thnAjaran.index') }}" class='sidebar-link'>
              <i class="bi bi-calendar3-week"></i>
              <span>Tahun Ajaran</span>
            </a>
          </li>
          <li class="sidebar-title">Penjadwalan</li>
          <li class="sidebar-item {{ request()->is('admin/plotting', 'admin/plotting/*') ? 'active' : '' }} ">
            <a href="{{ route('admin.plotting.index') }}" class='sidebar-link'>
              <i class="bi bi-calendar-heart"></i>
              <span>Plotting Mengajar</span>
            </a>
          </li>
          <li class="sidebar-item {{ request()->is('admin/guruFree', 'admin/guruFree/*') ? 'active' : '' }} ">
            <a href="{{ route('admin.guruFree.index') }}" class='sidebar-link'>
              <i class="bi bi-file-lock2"></i>
              <span>Atur Jadwal</span>
            </a>
          </li>
          <li class="sidebar-item {{ request()->is('admin/jadwal', 'admin/jadwal/*') ? 'active' : '' }}">
            <a href="{{ route('admin.jadwal.index') }}" class='sidebar-link'>
              <i class="bi bi-magic"></i>
              <span>Generate Jadwal</span>
            </a>
          </li>
        @endrole

        {{-- Menu Untuk Guru --}}
        @role('guru')
          <li class="sidebar-item {{ request()->is('guru/dashboard') ? 'active' : '' }} ">
            <a href="{{ route('guru.dashboard') }}" class='sidebar-link'>
              <i class="bi bi-grid-fill"></i>
              <span>Dashboard Guru</span>
            </a>
          </li>
          <li class="sidebar-title">Laporan</li>
          <li class="sidebar-item {{ request()->is('guru/jadwal-saya') ? 'active' : '' }} ">
            <a href="{{ route('guru.jadwal.saya') }}" class='sidebar-link'>
              <i class="bi bi-calendar-week"></i>
              <span>Jadwal Saya</span>
            </a>
          </li>
        @endrole

        {{-- Menu Untuk Kepsek --}}
        @role('kepsek')
          <li class="sidebar-item {{ request()->is('kepsek/dashboard') ? 'active' : '' }} ">
            <a href="{{ route('kepsek.dashboard') }}" class='sidebar-link'>
              <i class="bi bi-grid-fill"></i>
              <span>Dashboard Kepsek</span>
            </a>
          </li>
          <li class="sidebar-title">Laporan</li>
          <li class="sidebar-item {{ request()->is('kepsek/pemantauan') ? 'active' : '' }} ">
            <a href="{{ route('kepsek.pemantauan') }}" class='sidebar-link'>
              <i class="bi bi-calendar3"></i>
              <span>Jadwal Pelajaran Aktif</span>
            </a>
          </li>
        @endrole
      </ul>
    </div>
  </div>
</div>
