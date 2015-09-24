/**
 * This file is loaded in FormGenerator over \JS::registerJS for requests over cx.ajax
 *
 */

/**
 * Script for initializing the row sorting functionality in ViewGenerator 
 */
cx.jQuery(function(jQuery) {
    var cadminPath = cx.variables.get('cadminPath', 'contrexx'),
        component  = cx.variables.get('component', 'ViewGenerator/sortBy'),
        entity     = cx.variables.get('entity', 'ViewGenerator/sortBy'),
        sortField  = cx.variables.get('sortField', 'ViewGenerator/sortBy'),
        sortOrder  = cx.variables.get('sortOrder', 'ViewGenerator/sortBy'),
        jsonObject = cx.variables.get('jsonObject', 'ViewGenerator/sortBy'),
        jsonAct    = cx.variables.get('jsonAct', 'ViewGenerator/sortBy'),
        pagingPosition = cx.variables.get('pagingPosition', 'ViewGenerator/sortBy'),
        isSortByActive = cx.variables.get('isSortByActive', 'ViewGenerator/sortBy');
    if (typeof(isSortByActive) === 'undefined') {
        return;
    }
    
    jQuery('.adminlist').addClass('sortable');
    jQuery('.adminlist tbody').sortable({
        axis: "y",
        items: "> tr.row1,> tr.row2 ",
        start: function (event, ui) {
            jQuery(ui.item).data('pIndex', ui.item.index());
        },
        update: function (event, ui) {
            if (    typeof(jsonObject) === 'undefined'
                ||  typeof(jsonAct) === 'undefined'
                ||  !jsonObject
                ||  !jsonAct
            ) {
                return;
            }

            var that   = this,
                sortTd = jQuery('table.sortable tbody > tr:not(:nth-child(1), :nth-child(2), :last-child) > td.sortBy'),
                updatedOrder  = jQuery('.sortable tbody').sortable('serialize'), recordCount,
                currentIndex  = ui.item.index() - 1,
                previousIndex = jQuery(ui.item).data('pIndex') - 1,
                repeat = isOrderNoRepeat(sortTd, previousIndex, currentIndex),
                data   = 'sortOrder=' + sortOrder + '&curPosition=' + currentIndex
                       + '&prePosition=' + previousIndex
                       + '&sortField=' + sortField + '&pagingPosition=' + pagingPosition;
            if (component && entity) {
                data += '&component=' + component + '&entity=' + entity;
            }
            if (repeat) {
                data += '&' + updatedOrder;
            }
            jQuery(ui.item).removeData('pIndex');
            jQuery.ajax({
                type: 'POST',
                data: data,
                url:  cadminPath + 'index.php&cmd=JsonData&object=' + jsonObject + '&act=' + jsonAct,
                beforeSend: function() {
                    jQuery('body').addClass('loading');
                    jQuery(that).sortable("disable");
                    jQuery(ui.item).find('td:first-child').addClass('sorter-loading');
                },
                success: function(msg) {
                    if (msg.data.status == 'success') {
                        recordCount = msg.data.recordCount;
                    }
                },
                complete: function() {
                    updateOrder(sortTd, previousIndex, currentIndex, repeat, recordCount);
                    jQuery(that).sortable("enable");
                    jQuery('body').removeClass('loading');
                    jQuery(ui.item).find('td:first-child').removeClass('sorter-loading');
                }
            });
        }
    });

    //Check the same 'order' field value is repeated or not
    function isOrderNoRepeat(obj, pIndex, cIndex) {
        var orderArray = [], currentval,
            condition = cIndex > pIndex,
            min       = condition ? pIndex : cIndex,
            max       = condition ? cIndex : pIndex;
        while (min <= max) {
            currentval = condition ? obj.eq(min - 1).text() : obj.eq(max - 1).text();
            if (jQuery.inArray(currentval, orderArray) == -1) {
                orderArray.push(currentval);
                condition ? min++ : max--;
                continue;
            }
            return true;
        }
        return false;
    }

    //Update the sorted order in the 'order' field
    function updateOrder(obj, pIndex, cIndex, repeat, recordCnt) {
        var currentObj, currentOrder, order, firstObj,
            condition = cIndex > pIndex,
            min       = condition ? pIndex : cIndex,
            max       = condition ? cIndex : pIndex,
            first     = true;
    
        if (repeat) {
            var pagingCnt = (sortOrder == 'DESC') 
                            ? (recordCnt - pagingPosition) + 1
                            : pagingPosition;
            obj.each(function() {
                (sortOrder == 'DESC') ? pagingCnt-- : pagingCnt++;
                jQuery(this).text(pagingCnt);
            });
        } else {
            while (min <= max) {
                currentObj = condition ? obj.eq(min - 1) : obj.eq(max - 1);
                currentOrder = currentObj.text();
                if (first) {
                    first = false;
                    order = currentOrder;
                    firstObj = currentObj;
                    continue;
                } else if (min == max) {
                    firstObj.text(currentOrder);
                    currentObj.text(order);
                }
                currentObj.text(order);
                order = currentOrder;
                condition ? min++ : max--;
            }
        }
    }
});
jQuery(document).ready(function(){
    jQuery('.mappedAssocciationButton, .edit').click(function() {
        editAssociation(jQuery(this));
    });
});

function editAssociation (thisElement) {
    var paramAssociativeArray = {};
    if (jQuery(thisElement).attr("class").indexOf('mappedAssocciationButton') >= 0) {
        paramIndexedArray = jQuery(thisElement).attr('data-params').split(';');
    } else {
        paramIndexedArray = jQuery(thisElement).parent().siblings('.mappedAssocciationButton').attr('data-params').split(';');
    }

    jQuery.each(paramIndexedArray, function(index, value){
        paramAssociativeArray[value.split(':')[0]] = value.split(':')[1];
    });
    existingData = '';
    if (jQuery(thisElement).hasClass('edit')) {
        jQuery(thisElement).parent().children('input').addClass('current');
        existingData = jQuery(thisElement).parent().children('input').attr('value')
    }
    createAjaxRequest(
        paramAssociativeArray['entityClass'],
        paramAssociativeArray['mappedBy'],
        paramAssociativeArray['cssName'],
        paramAssociativeArray['sessionKey'],
        existingData
    );
}
/*
* This function creates a cx dialag for the ViewGenerator and opens it
*
*/
function openDialogForAssociation(content, className, existingData)
{

    buttons = [
        {
            text: cx.variables.get('TXT_CANCEL', 'Html/lang'),
            click: function() {
                jQuery(this).dialog('close');
                jQuery('.oneToManyEntryRow').children('.current').removeClass('current');
            }
        },
        {
            text: cx.variables.get('TXT_SUBMIT', 'Html/lang'),
            click: function() {

                var element = jQuery(this).closest('.ui-dialog').children('.ui-dialog-content').children('form');
                saveToMappingForm(element, className);
                jQuery(this).dialog('close');
            }
        }
    ];
    cx.ui.dialog({
        width: 600,
        height: 300,
        autoOpen: true,
        content: content,
        modal: true,
        resizable: false,
        buttons:buttons,
        close: function() {
            jQuery(this).dialog('close');
        }
    });
    jQuery.each(existingData.split('&'), function(index, value){
        property = value.split('=');
        jQuery('input[name='+property[0]+']').attr('value', property[1]);
        if (property[0] == 'id') {
            jQuery('<input>').attr({
                value: property[1],
                id: 'id',
                name: 'id',
                type: 'hidden'
            }).appendTo(jQuery('.ui-dialog-content').children('form'));
        }
    });


}

/*
 * This function takes the data from dialog form and writes it into our many form
 *
 */
function saveToMappingForm(element, className)
{
    value = element.serialize().split('&');
    var valuesAsString = '';
    jQuery.each(value, function(index, value){
        if(value.split('=')[0] != 'vg_increment_number' && value.split('=')[0] != 'id'){
            if (value.split('=')[1] != "") {
                decodedValue = value.split('=')[1];
                decodedValue = decodedValue.replace('+', ' '); // because serialize makes a plus out of whitespaces
                valuesAsString += decodeURIComponent(decodedValue) + ' / ';
            } else {
                valuesAsString += '-' + ' / ';
            }
        }
    });

    // if the last attribute is not set, we remove the notSetString "- / " for better optic
    while (valuesAsString.slice(-5) == ' - / ') {
        valuesAsString = valuesAsString.substr(0, valuesAsString.length - 5);
    }

    // remove the last slash and the last two whitespaces for better optic
    valuesAsString = valuesAsString.substr(0, valuesAsString.length - 3);

    // we only create a new element if it is not empty
    if (valuesAsString != "") {
        current = jQuery('.oneToManyEntryRow').children('.current');
        if(jQuery(current).is(':empty')){
            jQuery(current).attr('value', element.serialize());
            jQuery(current).parent().children('span').html(valuesAsString);
            jQuery(current).removeClass('current');
        } else {
            jQuery('.add_'+className+'').before('<div class=\'oneToManyEntryRow\'>'
                + '<span>' + valuesAsString + '</span>'
                + '<input type=\'hidden\' name=\'' + className + '[]\' value=\'' + element.serialize() + '\'>'
                + '<a onclick=\'editAssociation(this)\' class=\'edit\' title=\'' + cx.variables.get('TXT_EDIT', 'Html/lang') + '\'></a>'
                + '<a onclick=\'deleteAssociationMappingEntry(this)\' class=\'remove\' title=\'' + cx.variables.get('TXT_DELETE', 'Html/lang') + '\'></a>'
                + '</div>'
            );
        }
    }
}

/*
 * This function removes an association which we created over dialog
 *
 */
function deleteAssociationMappingEntry(element)
{
    // if we have an already existing entry (which is saved in the database), we only hide it, because we will remove
    // it as soon as the main formular is submitted.
    // otherwise we have an entry which doesn't exists in the database and we can simply remove the element, because we
    // do not need to store it and so it is useless
    if (jQuery(element).hasClass('existing')) {
        jQuery(element).parent().css('display', 'none');
        entryInput = jQuery(element).parent().children('input');
        entryInput.attr('value', entryInput.attr('value') + '&delete=1');
    } else {
        jQuery(element).parent().remove();
    }
}


/*
 * This function creates an ajax request to the ViewGenerator and on success call the function to open the dialog where
 * we can insert the data for the mapped association
 *
 */
function createAjaxRequest(entityClass, mappedBy, className, sessionKey, existingData){
    cx.ajax(
        'Html',
        'getViewOverJson',
    {
        data: {
            entityClass: entityClass,
            mappedBy:    mappedBy,
            sessionKey:  sessionKey
        },
        success: function(data) {
            openDialogForAssociation(
                data.data,
                className,
                existingData
            );
            jQuery('.datepicker').datepicker({
                dateFormat: 'dd.mm.yy'
            });
        }
    });
}
