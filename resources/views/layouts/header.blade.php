 <header>
   <nav class="navbar navbar-expand navbar-light navbar-top">
     <div class="container-fluid">
       <a href="javascript:void(0);" class="burger-btn d-block">
         <i class="bi bi-justify fs-3"></i>
       </a>

       <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
         aria-label="Toggle navigation">
         <span class="navbar-toggler-icon"></span>
       </button>
       <div class="collapse navbar-collapse" id="navbarSupportedContent">
         <div class="dropdown ms-auto">
           <a href="javascript:void(0)" data-bs-toggle="dropdown" aria-expanded="false">
             <div class="user-menu d-flex">
               <div class="user-name text-end me-3">
                 <h6 class="mb-0 text-gray-600">{{ Auth::user()->name }}</h6>
                 @if (Auth::user()->hasRole('admin') == 'admin')
                   <p class="mb-0 text-sm text-gray-600">Waka Kurikulum</p>
                 @elseif (Auth::user()->hasRole('guru') == 'guru')
                   <p class="mb-0 text-sm text-gray-600">Guru</p>
                 @else
                   <p class="mb-0 text-sm text-gray-600">Kepala Sekolah</p>
                 @endif
               </div>
               <div class="user-img d-flex align-items-center">
                 <div class="avatar avatar-md">
                   <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}">
                 </div>
               </div>
             </div>
           </a>
           <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton" style="min-width: 11rem;">
             </li>
             <li><a class="dropdown-item text-danger" href="{{ route('logout') }}"><i class="icon-mid bi bi-box-arrow-left me-2"></i> Logout</a></li>
           </ul>
         </div>
       </div>
     </div>
   </nav>
 </header>
