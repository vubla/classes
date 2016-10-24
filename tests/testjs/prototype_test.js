
test('sanitycheck',function(){
    notEqual($, undefined);
    notEqual($(document), undefined);
});

test('Check for dispatch event',function() {
    notEqual($('qunit-fixture').dispatchEvent, undefined, 'needed functionality present');
});
