
{{ content() }}

<div class="page-header">
    <h2>Авторизация</h2>
</div>

{{ form('authorized', 'id': 'authorizedForm', 'onbeforesubmit': 'return false') }}

    <fieldset>

         {% for element in form %}
                    <div class="control-group">
                        {{ element.label(["class": "control-label"]) }}

                        <div class="control">
                            {{ element }}
                        </div>
                    </div>
         {% endfor %}
         <div class="form-actions">
                     {{ submit_button('Войти', 'class': 'btn btn-primary', 'onclick': 'return SignUp.validate();') }}
                     <p class="help-block">Регистрируясь вы подтверждаете, что принимаете пользовательское соглашение.</p>
                 </div>

    </fieldset>
</form>
