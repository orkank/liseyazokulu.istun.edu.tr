{%include file='_partials/header.tpl' %}

  <div class="content-wrapper">
    <div class="container mt-5">
      <div class="row">
        <div class="col-12">
          <div id="content-wrapper">
            <h1 class="mb-5">{%$content.name%}</h1>
            {%$content.content%}
          </div>
        </div>
      </div>
    </div>
  </div>

{%include file='_partials/footer.tpl' %}