<footer class="w-full bg-[#f5f5f5] text-[#333] font-sans">
  <!-- TOP NEWSLETTER BAR -->
  <div class="w-full bg-[#07244a]">
    <!-- Responsivitas: flex-col di mobile, flex-row di desktop -->
   

      <!-- Responsivitas: Hilangkan fixed width, ganti ke w-full -->
  </div>

  <!-- MAIN FOOTER -->
  <div class="max-w-[1500px] mx-auto px-6 md:px-16 pt-12 md:pt-16 pb-14">
    
    <!-- Responsivitas: Grid 1 kolom di mobile, 2 di tablet, 4 di desktop -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12 lg:gap-24">
      
      <!-- LEFT SECTION (Logo & Addresses) -->
      <div class="flex flex-col sm:flex-row lg:flex-col gap-8 lg:col-span-1">
        <!-- LOGO -->
        <div class="flex-shrink-0">
          <div class="text-[#07244a] font-bold leading-[0.8] text-[34px]">
            <div>OC</div>
          </div>
        </div>

        <div class="space-y-10 md:space-y-14">
          <div>
            <h3 class="font-semibold text-[18px] md:text-[20px] mb-3">The OC Fifth Avenue</h3>
            <p class="text-[16px] md:text-[18px] leading-[1.8] text-[#555]">
              1000 Fifth Avenue<br />
              New York, NY 10028<br />
              212-535-7710
            </p>
          </div>

          <div>
            <h3 class="font-semibold text-[18px] md:text-[20px] mb-3">The OC Cloisters</h3>
            <p class="text-[16px] md:text-[18px] leading-[1.8] text-[#555]">
              99 Margaret Corbin Drive<br />
              New York, NY 10040<br />
              212-923-3700
            </p>
          </div>
        </div>
      </div>

      <!-- ABOUT -->
      <div>
        <h3 class="font-semibold text-[18px] md:text-[20px] mb-5">About The OC</h3>
        <ul class="space-y-4 md:space-y-5 text-[16px] md:text-[18px] text-[#555]">
      
          <li><a href="{{ route('art.curatorial-areas') }}" class="hover:underline">Collection Areas</a></li>
          <li><a href="#" class="hover:underline">Conservation Departments</a></li>
          <li><a href="{{ route('visit.accessibility') }}" class="hover:underline">Accessibility</a></li>
         
        </ul>
      </div>

      <!-- SUPPORT -->
      <div>
        <h3 class="font-semibold text-[18px] md:text-[20px] mb-5">Support</h3>
        <ul class="space-y-4 md:space-y-5 text-[16px] md:text-[18px] text-[#555]">
          <li><a href="{{ route('membership.index') }}" class="hover:underline">Membership</a></li>
          
        </ul>
      </div>

       
      

    </div>
  </div>

  <!-- BOTTOM -->
  <div class="border-t border-[#dfdfdf]">
    <div class="max-w-[1500px] mx-auto px-6 md:px-16 py-8 flex flex-col md:flex-row items-center justify-between gap-6 text-[14px] md:text-[15px] text-[#555] text-center md:text-left">
      <div class="flex flex-wrap justify-center md:justify-start gap-4 md:gap-8">
        <a href="#" class="hover:underline">Terms and Conditions</a>
        <a href="#" class="hover:underline">Privacy Policy</a>
        <a href="#" class="hover:underline">Contact Information</a>
      </div>
      <p>© 2000–2026 Our Civilization of Art.</p>
    </div>
  </div>
</footer>