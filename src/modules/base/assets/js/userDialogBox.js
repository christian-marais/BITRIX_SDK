BX24.selectUsers(
    function(params)
    {
        for (var i in params)
        {
            let param = params[i];
            BX('student' + i).value = param.name;
            BX('student_external_id'  + i).value = param.id;
        }
    }
);