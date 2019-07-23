$(document).ready(function () {
    var csrfToken = $('input[name=csrfToken]').val();
    var articleId = $('input[name=articleId]').val();
    var showdownConverter = new showdown.Converter();
    
    var previewDelayTimer = null;
    function redrawPreview()
    {
//        console.log("Updating preview...");
        $('#ogCardPreviewImage').attr('src', $('#openGraphImageUrl').val());
        $('#ogCardPreviewSummary').html(
                '<span class="title">' + $('#articleTitle').val() + '</span>' +
                $('#articleSummary').val().substr(0, 165));
        
        $('#livePreview .post-title').html($('#articleTitle').val());
        $('#livePreview .post-body').html(
                showdownConverter.makeHtml($('#articleBody').val())
                );
        
        Prism.highlightAll();
    }
    
    $('#editPost').on('keyup', function (e){
        if (previewDelayTimer !== null) {
            clearTimeout(previewDelayTimer);
        }
        previewDelayTimer = setTimeout(function (){ redrawPreview(); }, 500);
    });
    
    $('#openGraphImageUrl').on('change', function (e) {
        let $el = $(this);
        $el.addClass('updating');
        $el.removeClass('updateFailed');
        $el.removeClass('updateSuccessful');
        $.post('/admin/editarticle', {
            className: 'Article.ChangeOpenGraphImage',
            csrfToken: csrfToken,
            articleId: articleId,
            openGraphImageUrl: $('#openGraphImageUrl').val()
        })
                .done(function (data) {
                    $el.addClass('updateSuccessful');
                    $el.removeClass('updating');
                    $('#ogCardPreviewImage').attr('src', $('#openGraphImageUrl').val());
                })
                .fail(function (data) {
                    $el.addClass('updateFailed');
                    $el.removeClass('updating');
                    console.log("Failed!", data);
                });
    });

    $('#articleTitle').on('change', function (e) {
        let $el = $(this);
        $el.addClass('updating');
        $el.removeClass('updateFailed');
        $el.removeClass('updateSuccessful');
        $.post('/admin/editarticle', {
            className: 'Article.ChangeTitle',
            csrfToken: csrfToken,
            articleId: articleId,
            articleTitle: $('#articleTitle').val()
        })
                .done(function (data) {
                    $el.addClass('updateSuccessful');
                    $el.removeClass('updating');

                })
                .fail(function (data) {
                    $el.addClass('updateFailed');
                    $el.removeClass('updating');
                    console.log("Failed!", data);
                });
    });
    
    $('#articleTags').on('change', function (e) {
        let $el = $(this);
        $el.addClass('updating');
        $el.removeClass('updateFailed');
        $el.removeClass('updateSuccessful');
        $.post('/admin/editarticle', {
            className: 'Article.ChangeTags',
            csrfToken: csrfToken,
            articleId: articleId,
            tags: $('#articleTags').val()
        })
                .done(function (data) {
                    $el.addClass('updateSuccessful');
                    $el.removeClass('updating');

                })
                .fail(function (data) {
                    $el.addClass('updateFailed');
                    $el.removeClass('updating');
                    console.log("Failed!", data);
                });
    });

    $('#articleSlug').on('change', function (e) {
        let $el = $(this);
        $el.addClass('updating');
        $el.removeClass('updateFailed');
        $el.removeClass('updateSuccessful');
        $.post('/admin/editarticle', {
            className: 'Article.ChangeSlug',
            csrfToken: csrfToken,
            articleId: articleId,
            articleSlug: $('#articleSlug').val()
        })
                .done(function (data) {
                    $el.addClass('updateSuccessful');
                    $el.removeClass('updating');

                })
                .fail(function (data) {
                    $el.addClass('updateFailed');
                    $el.removeClass('updating');
                    console.log("Failed!", data);
                });
    });

    $('#articleSummary').on('change', function (e) {
        let $el = $(this);
        $el.addClass('updating');
        $el.removeClass('updateFailed');
        $el.removeClass('updateSuccessful');
        $.post('/admin/editarticle', {
            className: 'Article.ChangeSummary',
            csrfToken: csrfToken,
            articleId: articleId,
            articleSummary: $('#articleSummary').val()
        })
                .done(function (data) {
                    $el.addClass('updateSuccessful');
                    $el.removeClass('updating');
                    
                })
                .fail(function (data) {
                    $el.addClass('updateFailed');
                    $el.removeClass('updating');
                    console.log("Failed!", data);
                });
    });

    $('#articleBody').on('change', function (e) {
        let $el = $(this);
        $el.addClass('updating');
        $el.removeClass('updateFailed');
        $el.removeClass('updateSuccessful');
        $.post('/admin/editarticle', {
            className: 'Article.ChangeBody',
            csrfToken: csrfToken,
            articleId: articleId,
            articleBody: $('#articleBody').val()
        })
                .done(function (data) {
                    $el.addClass('updateSuccessful');
                    $el.removeClass('updating');

                })
                .fail(function (data) {
                    $el.addClass('updateFailed');
                    $el.removeClass('updating');
                    console.log("Failed!", data);
                });
    });

    $('#publishArticle').on('click', function (e) {
        let $el = $(this);
        $el.addClass('updating');
        $el.removeClass('updateFailed');
        $el.removeClass('updateSuccessful');
        $.post('/admin/editarticle', {
            className: 'Article.Publish',
            csrfToken: csrfToken,
            articleId: articleId
        })
                .done(function (data) {
                    $el.addClass('updateSuccessful');
                    $el.removeClass('updating');
                })
                .fail(function (data) {
                    $el.addClass('updateFailed');
                    $el.removeClass('updating');
                    console.log("Failed!", data);
                });
    });
});
