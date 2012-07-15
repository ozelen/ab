
<!DOCTYPE html>
<html>
    <head>
        <script src="/js/jquery/jquery-1.5.1.min.js"></script>
    </head>
    <body>



        <FORM name=f4>
            <SELECT name="select" id="111">
                <OPTION  value="http://kiev-stroi.com.ua/loan2">
                    Приват банк
                <OPTION selected value="http://kiev-stroi.com.ua">
                    Аваль банк<OPTION value="http://litzone.org.ua">
                Укргазбанк
            </SELECT>

            <input onclick="document.location.href=$('select option:selected').val();" type="button" value="Перейти" />


        </FORM>
    </body>
</html>