<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("auctionsModer", "Назад") }}</li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Создание тендера
    </h1>
</div>

{{ content() }}

{{ form("auctionsModer/create", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldTaskid" class="col-sm-2 control-label">ID задания</label>
    <div class="col-sm-10">
        <!--{{ text_field("taskId", "type" : "numeric", "class" : "form-control", "id" : "fieldTaskid") }}-->
         {{ select('taskId', tasks, 'using':['taskId', 'name'],'useEmpty':true,
             'emptyValue':null, 'emptyText':'', 'class':'form-control', 'id':'fieldTaskId') }}
    </div>
</div>

<div class="form-group">
    <label for="fieldSelectedoffer" class="col-sm-2 control-label">Выбранное предложение (ID)</label>
    <div class="col-sm-10">
        <!--{{ text_field("selectedOffer", "type" : "numeric", "class" : "form-control", "id" : "fieldSelectedoffer") }}-->
        {{ select('selectedOffer', offers, 'using':['offerId', 'description'],'useEmpty': true, 'emptyValue':null, 'emptyText':'', 'class':'form-control', 'id':'fieldSelectedOffer')}}

    </div>
</div>

<div class="form-group">
    <label for="fieldDatestart" class="col-sm-2 control-label">Дата начала</label>
    <div class="col-sm-10">
        {{ date_field("dateStart", "size" : 30, "class" : "form-control", "id" : "fieldDatestart") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDateend" class="col-sm-2 control-label">Дата завершения</label>
    <div class="col-sm-10">
        {{ date_field("dateEnd", "size" : 30, "class" : "form-control", "id" : "fieldDateend") }}
    </div>
</div>


<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        {{ submit_button('Создать', 'class': 'btn btn-default') }}
    </div>
</div>

</form>
