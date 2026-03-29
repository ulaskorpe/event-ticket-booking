async function save(formData,route,formID,btn,reload) {
    // Show loading state
    if(btn!='') {
        $('#'+btn).prop('disabled', true);
        // Try different possible text/loading ID patterns
        var textId = '';
        var loadingId = '';
        
        // Pattern 1: submit_button_message_admin -> submit_text_message_admin
        if (btn.includes('submit_button_')) {
            textId = '#' + btn.replace('submit_button_', 'submit_text_');
            loadingId = '#' + btn.replace('submit_button_', 'submit_loading_');
        } 
        // Pattern 2: submit_button -> submit_text
        else if (btn.includes('submit_button')) {
            textId = '#' + btn.replace('submit_button', 'submit_text');
            loadingId = '#' + btn.replace('submit_button', 'submit_loading');
        }
        
        // Check if custom IDs exist, otherwise use default pattern
        if (textId && $(textId).length > 0) {
            $(textId).hide();
        } else if ($('#'+btn+' #submit_text').length > 0) {
            $('#'+btn+' #submit_text').hide();
        }
        
        if (loadingId && $(loadingId).length > 0) {
            $(loadingId).show();
        } else if ($('#'+btn+' #submit_loading').length > 0) {
            $('#'+btn+' #submit_loading').show();
        }
    }
 
    return $.ajax({
        url:  route,
        type: 'POST',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        success: function (data) {
            if (data && data.status === 'error') {
                Swal.fire({
                    title: 'Hata!',
                    text: data.message || 'Bir hata oluştu.',
                    icon: 'error',
                    confirmButtonText: 'Tamamdır'
                }).then(function() {
                    if (btn !== '') resetButtonState(btn);
                });
                return;
            }
            // Response'daki redirect URL'ini al (data.data.redirect veya data.redirect)
            var redirectUrl = (data && data.data && data.data.redirect) ? data.data.redirect : (data && data.redirect) ? data.redirect : null;
            if (redirectUrl && typeof redirectUrl === 'string' && redirectUrl.length > 0) {
                if (btn !== '') resetButtonState(btn);
                Swal.fire({
                    title: 'Tebrikler',
                    text: data.message || 'İşlem başarılı.',
                    icon: 'success',
                    confirmButtonText: 'Tamamdır'
                }).then(function() {
                    window.location.href = redirectUrl;
                });
                return;
            }
            // Redirect yoksa normal başarı akışı (reload vb.)
            Swal.fire({
                title: 'Tebrikler',
                text: data.message || 'İşlem başarılı.',
                icon: 'success',
                confirmButtonText: 'Tamamdır'
            }).then(function() {
                if (btn !== '') resetButtonState(btn);
                if (formID && (formID.includes('message_form') || formID === 'message_form_admin' || formID === 'message_form_user')) {
                    resetMessageForm(formID);
                }
            });
            if (reload) {
                if (reload === 'reload') {
                    setTimeout(function() { location.reload(); }, 1000);
                } else if (typeof reload === 'string' && reload.startsWith('redirect:')) {
                    setTimeout(function() { window.location.href = reload.replace('redirect:', ''); }, 1000);
                } else {
                    setTimeout(function() { window.location.href = reload; }, 1000);
                }
            }
        },
        error: function (data) {
            // Hata durumunda butonu hemen varsayılana döndür (Swal kapanmasını beklemeden)
            if (btn !== '') {
                resetButtonState(btn);
            }
            var errorMessage = 'Bir hata oluştu.';

            // Önce responseJSON'dan message'ı kontrol et (tüm status kodları için - öncelikli)
            if (data.responseJSON && data.responseJSON.message) {
                errorMessage = data.responseJSON.message;
            } else if (data.status === 422) {
                // 422 durumunda message yoksa errors key'ini kontrol et (Laravel validation format)
                if (data.responseJSON && data.responseJSON.errors) {
                    var errors = data.responseJSON.errors;
                    var message = "";
                    $.each(errors, function (key, value) {
                        if (Array.isArray(value)) {
                            message += key + ' : ' + value.join(', ') + '\n';
                        } else {
                            message += key + ' : ' + value + '\n';
                        }
                    });
                    errorMessage = message.trim() || 'Form doğrulama hatası';
                } else {
                    errorMessage = 'Form doğrulama hatası';
                }
            } else if (data.status === 500) {
                // Backend'den gelen mesajı kontrol et
                if (data.responseText) {
                    // Eğer JSON değilse responseText'i parse etmeyi dene
                    try {
                        var parsedResponse = JSON.parse(data.responseText);
                        if (parsedResponse.message) {
                            errorMessage = parsedResponse.message;
                        }
                    } catch(e) {
                        // JSON parse edilemezse genel mesaj göster
                        errorMessage = 'Sunucu hatası oluştu. Lütfen tekrar deneyin.';
                    }
                } else {
                    errorMessage = 'Sunucu hatası oluştu. Lütfen tekrar deneyin.';
                }
            } else if (data.responseText) {
                // JSON gelmediyse ham response'tan kısa bir özet çıkar
                try {
                    var parsedGeneric = JSON.parse(data.responseText);
                    if (parsedGeneric && parsedGeneric.message) {
                        errorMessage = parsedGeneric.message;
                    } else {
                        errorMessage = String(data.responseText).substring(0, 500);
                    }
                } catch (e) {
                    var raw = String(data.responseText)
                        .replace(/<[^>]+>/g, ' ')
                        .replace(/\s+/g, ' ')
                        .trim();
                    if (raw) {
                        errorMessage = raw.substring(0, 500);
                    }
                }
            }

            console.error('AJAX error details:', {
                status: data.status,
                statusText: data.statusText,
                responseJSON: data.responseJSON,
                responseText: data.responseText
            });

            Swal.fire({
                title: 'Hata!',
                text: errorMessage,
                icon: 'error',
                confirmButtonText: 'Tamamdır'
            });
        },
        complete: function() {
            // Error durumunda buton zaten aktif edildi, success durumunda Swal kapandıktan sonra aktif edilecek
            // Burada sadece error durumunda veya Swal gösterilmeyen durumlarda aktif et
            // (success durumunda Swal.then() içinde aktif ediliyor)
        }
    });
}

// Buton durumunu sıfırla
function resetButtonState(btn) {
    var $el = $('#'+btn);
    $el.prop('disabled', false);
    $el.removeAttr('disabled');
    var textId = '';
    var loadingId = '';
    
    // Pattern 1: submit_button_message_admin -> submit_text_message_admin
    if (btn.includes('submit_button_')) {
        textId = '#' + btn.replace('submit_button_', 'submit_text_');
        loadingId = '#' + btn.replace('submit_button_', 'submit_loading_');
    } 
    // Pattern 2: submit_button -> submit_text
    else if (btn.includes('submit_button')) {
        textId = '#' + btn.replace('submit_button', 'submit_text');
        loadingId = '#' + btn.replace('submit_button', 'submit_loading');
    }
    
    if (textId && $(textId).length > 0) {
        $(textId).show();
    } else if ($('#'+btn+' #submit_text').length > 0) {
        $('#'+btn+' #submit_text').show();
    }
    
    if (loadingId && $(loadingId).length > 0) {
        $(loadingId).hide();
    } else if ($('#'+btn+' #submit_loading').length > 0) {
        $('#'+btn+' #submit_loading').hide();
    }
}

// Mesaj formunu sıfırla
function resetMessageForm(formID) {
    var form = document.getElementById(formID);
    if (form) {
        form.reset();
        // Checkbox'ı varsayılan duruma getir (admin formunda checked, user formunda unchecked)
        if (formID === 'message_form_admin') {
            $('#system_message_admin').prop('checked', true);
        } else if (formID === 'message_form_user') {
            $('#system_message_user').prop('checked', false);
        }
        // Hata sınıflarını temizle
        $(form).find('.border-danger').removeClass('border-danger');
    }
}
