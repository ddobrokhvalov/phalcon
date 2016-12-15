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
        if (value[0] != 0 || value.length != 19)
            return false;

        return true;
    },
    email: function (email) {

        var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    },
    emptyText: function (text, min) {
        if (text.length < min)
            return false;
        return true;
    },

    post: function(post){
        var re = /^[\d]{6}$/;
        return re.test(post)
    }
}
