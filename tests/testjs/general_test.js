
test('sanitycheck',function(){
    notEqual(jQuery, undefined);
    notEqual($, undefined);
    notEqual(jQuery(document), undefined);
    notEqual($(document), undefined);
    equal(jQuery, $, 'jQuery loaded correctly');
    notEqual(jQuery().live, undefined, 'needed functionality present');
});

test('autocomplete attribute off',function() {
    strictEqual($('#vbl-search-field').attr('autocomplete'),'off');
});

test('get suggestion',function() {
    var value = 'lås';
    vbl_client_host = 'safegear.dk';
    skip_suggestion = false;
    $('#vbl-search-field').attr('value',value);
    QUnit.stop(1);
    ok(getSuggestions($('#vbl-search-field'),function() {
        strictEqual($('#vbl-search-field').attr('value'),value);
        notEqual(jQuery('#vbl-suggestions').html(),null);
        notEqual(jQuery('#vbl-suggestions .vbl-suggestion').html(),null);
        equal(jQuery('#vbl-suggestions .vbl-suggestion').size(),5);
        QUnit.start(1);
    }));
});

test('get suggestion no result',function() {
    var value = 'abc';
    vbl_client_host = 'safegear.dk';
    skip_suggestion = false;
    $('#vbl-search-field').attr('value',value);
    QUnit.stop(1);
    ok(getSuggestions($('#vbl-search-field'),function() {
        strictEqual($('#vbl-search-field').attr('value'),value);
        equal(jQuery('#vbl-suggestions').html(),null);
        QUnit.start(1);
    }));
});

test('get suggestion skip',function() {
    var value = 'lås';
    vbl_client_host = 'safegear.dk';
    skip_suggestion = true;
    $('#vbl-search-field').attr('value',value);
    equal(getSuggestions($('#vbl-search-field')),false);
    strictEqual($('#vbl-search-field').attr('value'),value);
    equal(jQuery('#vbl-suggestions').html(),null);

});

test('get suggestion removing old',function() {
    var value = 'lås';
    vbl_client_host = 'safegear.dk';
    skip_suggestion = false;
    $('#vbl-search-field').attr('value',value);
    QUnit.stop(1);
    ok(getSuggestions($('#vbl-search-field'),function() {
        strictEqual($('#vbl-search-field').attr('value'),value);
        notEqual(jQuery('#vbl-suggestions').html(),null);
        notEqual(jQuery('#vbl-suggestions .vbl-suggestion').html(),null);
        QUnit.start(1);
        
        value = 'abc';
        $('#vbl-search-field').attr('value',value);
        QUnit.stop(1);
        ok(getSuggestions($('#vbl-search-field'),function() {
            strictEqual($('#vbl-search-field').attr('value'),value);
            equal(jQuery('#vbl-suggestions').html(),null);
            QUnit.start(1);
            
        }));
    }));
});

test('get suggestion too short with spaces',function() {
    var value = '   a   ';
    vbl_client_host = 'safegear.dk';
    skip_suggestion = false;
    $('#vbl-search-field').attr('value',value);
    equal(getSuggestions($('#vbl-search-field')),false);
    
    strictEqual($('#vbl-search-field').attr('value'),value);
    equal(jQuery('#vbl-suggestions').html(),null);
});

test('get suggestion show only latest',function() {
    var value = 'lås';
    vbl_client_host = 'safegear.dk';
    skip_suggestion = false;
    $('#vbl-search-field').attr('value',value);
    QUnit.stop(2);
    ok(getSuggestions($('#vbl-search-field'), function() {
        //Nothing should have been inserted yet
        equal(jQuery('#vbl-suggestions').html(),null);
        QUnit.start(1);
    }));
    
    value = 'låsekasse';
    $('#vbl-search-field').attr('value',value);
    ok(getSuggestions($('#vbl-search-field'),function() {
        strictEqual($('#vbl-search-field').attr('value'),value);
        notEqual(jQuery('#vbl-suggestions').html(),null);
        notEqual(jQuery('#vbl-suggestions .vbl-suggestion').html(),null);
        equal(jQuery('#vbl-suggestions .vbl-suggestion').size(),3);
        QUnit.start(1);
    }));
    
    
});
