var validator = {

    text: function (text, min, max) {
        if (text.length < min || text.length > max)
            return false;
        return true;
    },
    numeric: function (value, min, max) {
        if (value.length < min || value.length > max)
            return false;
        if (parseInt(value) != value)
            return false;

        return true;
    },
    email: function (email) {

        var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }

}
