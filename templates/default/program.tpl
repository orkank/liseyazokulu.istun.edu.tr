<div class="lessonModals" style="padding: 10px; display: none;">
  <div class="card">
    <div class="card-body p-4">
      <div class="table-custom">
        <!-- <h1 class="mb-4">{%$program['name']%}</h1> -->
        <div class="d-lg-flex flex-row gap-2 align-items-center justify-content-between">
          <div class="d-lg-flex flex-row gap-2 align-items-start">
            {%if !empty($program['nodes']['page_video'][0]) %}
              <video style="max-width: 320px; object-fit: cover;height:230px;" class="rounded" autoplay muted playsinline loop>
                <source src="{%$program['nodes']['page_video'][0]%}" type="video/mp4">
              </video>
            {%/if%}
            {%if !empty($program['nodes']['page_image'][0])%}
              <img src="{%$program['nodes']['page_image'][0]%}" style="max-width: 320px; object-fit: cover;height:230px;" class="rounded" alt="{%$program['name']%}">
            {%/if%}

            <p class="ps-lg-4">
              <span class="dropcap text-primary">
                {%$program['nodes']['description']|mb_substr:0:1:'UTF-8'%}
              </span>
              {%$program['nodes']['description']%}
            </p>
          </div>

          {%if $options.basvuru%}
            <button class="btn btn-sm btn-outline-primary rounded-pill align-self-start" data-add-program="{%$program['id']%}"
              data-date-start="{%$program['nodes']['start_date']%}"
              data-date-end="{%$program['nodes']['end_date']%}"
              ><i class="uil uil-plus"></i> Listeme Ekle</button>
          {%/if%}
        </div>

        <div class="divider-icon my-8 mb-15"><i class="uil uil-university"></i></div>

        <div class="header d-flex flex-column flex-lg-row justify-content-center gap-2 align-items-center"><strong>{%$program['name']%}</strong>
          <span class="badge bg-pale-navy text-primary d-inline-block">{%$program['nodes']['date']%}</span>
        </div>

        {%if isset($program['nodes']['tabs'])%}
            <ul class="nav nav-tabs nav-tabs-basic">
              {%foreach $program['nodes']['tabs']['name'] as $key => $day%}
                <li class="nav-item"> <a class="nav-link {%if $key == 0%}active{%/if%}" data-bs-toggle="tab" href="#tab3-{%$program['id']%}-{%$key%}">
                  {%$day%}
                </a> </li>
              {%/foreach%}
            </ul>
            <!-- /.nav-tabs -->
            <div class="tab-content mt-0 mt-md-5">
              {%foreach $program['nodes']['tabs']['text'] as $key => $day%}
                <div class="tab-pane fade {%if $key == 0%}show active{%/if%}" id="tab3-{%$program['id']%}-{%$key%}">
                  <div class="table-responsive w-100">
                    {%$day%} <!-- table -->
                  </div>
                </div>
                <!--/.tab-pane -->
              {%/foreach%}
            </div>
            <!-- /.tab-content -->
        {%/if%}

      </div>
    </div>
    <div class="card-footer">
      <div class="d-flex flex-row gap-2 align-items-center justify-content-between">
        <small>İstanbul Sağlık ve Teknoloji Üniversitesi <script> document.write(new Date().getUTCFullYear()); </script></small>
        <button onclick="Fancybox.close();" class="btn btn-sm btn-secondary rounded-pill align-self-center"><i class="uil uil-times"></i> Kapat</button>
      </div>
    </div>
  </div>
</div>
