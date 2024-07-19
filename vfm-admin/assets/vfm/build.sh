cd `dirname $0`

uglifyjs ../soundmanager/soundmanager2-nodebug-jsmin.js js/vfm-inlineplayer.js ../initial/initial.min.js ../cropit/jquery.cropit.min.js js/avatars.js ../bootbox/bootbox.min.js ../datatables/datatables.min.js ../clipboard/clipboard.min.js ../uploaders/resumable.js ../uploaders/jquery.form.min.js js/uploaders.js js/app.js -o ../../js/vfm-bundle.min.js

uglifyjs js/registration.js -o ../../js/registration.min.js

cleancss -o ../../css/vfm-bundle.min.css ../datatables/datatables.min.css ../plyr/plyr.css css/vfm-style.css