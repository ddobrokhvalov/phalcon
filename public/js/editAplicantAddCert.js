$(document).ready(function () {
    $(".addAppCertificate__addBtn_edit").on('click', function(){
        if (selectedCertif == false)
            return;
        applicant.edit_mode = true;
        $('.addAppCertificate-main2').fadeOut().css('display', 'none');

        var userData = selectedCertif.SubjectName;

        if (userData.indexOf('OGRN=') != -1) {
            applicant.parseUrLico(selectedCertif);
        }
        if (userData.indexOf('OGRNIP=') != -1) {
            applicant.parseIp(selectedCertif);
        }

        var str = selectedCertif.ValidFromDate;
        str = str.toString().substr(0, 10);
        var field = str + ' | ' + selectedCertif.SubjectDNSName;

        // $('.ecp_ur').val(selectedCertif.Thumbprint);
        // $('.ecp_text').val(field);

        count_ecp++;
        var str = '<li class="apCerList__apCeritem apCerItem" data-thumbprint="'+ selectedCertif.Thumbprint +'">'+ field;
        str += '<input type="hidden" name="cert['+ count_ecp +'][name]" value="'+ field +'">';
        str += '<input type="hidden" name="cert['+ count_ecp +'][thumbprint]" value="'+ selectedCertif.Thumbprint +'">';
        str += '</li>';
        $("#mCSB_2_container, #mCSB_3_container").append(str);


        $('.content').removeClass('hiddenClass');
    });

    $('.applicantCertificate__remove ').on('click', function(){
        $('.apCerItem-active').remove();
    });

    $('.applicantCertificate__add').on('click', function(){
        findCertificates();
        $('.addAppCertificate').toggle();
    });

    $('.addAppCertificate__cancelBtn').on('click', function(){
        $('.addAppCertificate').toggle();
    });
});
