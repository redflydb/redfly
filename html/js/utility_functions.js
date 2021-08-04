// ==========================================================================================
// Various utility functions that should be global and can be used by multiple components
// ==========================================================================================
Ext.namespace('REDfly.fn');
// ------------------------------------------------------------------------------------------
// Calculate the coordinates for the next window. If the windows are not being tiled, use the
// defined x & y offset. If the windows are being tiled using the window witdh. Wrap windows
// if the x coordinate would put a window off of the visible area of the browser.
// Parameters:
//      x: Current x-coordinate
//      y: Current y-coordinate
// Returns:
//      The new coordinates as Array(newX, newY)
// ------------------------------------------------------------------------------------------
REDfly.fn.getNextWindowCoordinates = function(
    x,
    y
) {
    if ( REDfly.state.tileWindows ) {
        // Advance the horizontal position
        x += REDfly.config.windowWidth;
        // If the width of the new window exceeds then the available space move to the next row
        if ( x + REDfly.config.windowWidth >= REDfly.config.windowAreaMaxWidth ) {
            x =  REDfly.config.windowAreaStartCoordinates[0];
            y = y + REDfly.config.windowHeight;
        }
    } else {
        // Advance the horizontal position
        x += REDfly.config.nextWindowOffset[0];
        // If the width of the new window exceeds the available space then simply reset the 
        // x-coordinates to the left-most position and don't increment the y-coordinates.
        if ( x + REDfly.config.windowWidth >= REDfly.config.windowAreaMaxWidth ) {
            x =  REDfly.config.windowAreaStartCoordinates[0];
        } else {
            y += REDfly.config.nextWindowOffset[1];
        }
    }

    return Array(x, y)
}
// ------------------------------------------------------------------------------------------
// Tile the entity windows in a side-by-side grid to the right of the search panel.
// Parameters:
//      windowGroup: Ext.WindowGroup() to manage entity windows
// ------------------------------------------------------------------------------------------
REDfly.fn.tileWindows = function(windowGroup) {
    var first = REDfly.state.windowGroup.getActive();
    var currentWindow;
    if ( ! first ) return;
    // Pull the initial (x, y) based on the location of the window area.
    var x = REDfly.config.windowAreaStartCoordinates[0];
    var y = REDfly.config.windowAreaStartCoordinates[1];
    first.setPosition(x, y);
    // Sending to the back makes this not the active window
    REDfly.state.windowGroup.sendToBack(first);
    while ( (currentWindow = REDfly.state.windowGroup.getActive()) != first ) {
        // Advance the horizontal position
        x += REDfly.config.windowWidth;
        // If the width of the new window exceeds the available space then  move to the next row
        if ( x + REDfly.config.windowWidth >= REDfly.config.windowAreaMaxWidth ) {
            x =  REDfly.config.windowAreaStartCoordinates[0];
            y = y + REDfly.config.windowHeight;
        }
        currentWindow.setPosition(x, y);
        REDfly.state.windowGroup.sendToBack(currentWindow);
    }
    // Reset all of the windows to the same size
    REDfly.state.windowGroup.each(function(w) {
        w.setWidth(REDfly.config.windowWidth);
        w.setHeight(REDfly.config.windowHeight);
    });
    REDfly.config.nextWindowCoordinates = REDfly.fn.getNextWindowCoordinates(x, y);
}
// ------------------------------------------------------------------------------------------
// Close all windows in the window group and resets the next window coordinates to the upper
// left.
// Parameters:
//      windowGroup: Ext.WindowGroup() to manage entity windows
// ------------------------------------------------------------------------------------------
REDfly.fn.closeAllWindows = function() {
    var currentWindow;
    while ( (currentWindow = REDfly.state.windowGroup.getActive()) ) {
        currentWindow.close();
    }
    // Reset next window coordinates
    REDfly.fn.resetWindowCoordinates();
}
// ------------------------------------------------------------------------------------------
// Reset the next window coordinates, for example this can happen if all windows are closed
// or if the "tile windows" button is turned off.
// ------------------------------------------------------------------------------------------
REDfly.fn.resetWindowCoordinates = function() {
    // If there are no windows currently open, set the next window coordinates to the start
    // coordinates. Otherwise set the coordinates to the start plus offset so we don't put
    // a new window over an existing window already at the start position.
    if ( ! REDfly.state.windowGroup.getActive() ) {
        REDfly.config.nextWindowCoordinates = REDfly.config.windowAreaStartCoordinates;
    } else {
        REDfly.config.nextWindowCoordinates = REDfly.fn.getNextWindowCoordinates(
            REDfly.config.windowAreaStartCoordinates[0],
            REDfly.config.windowAreaStartCoordinates[1]
        );
    }
}
// ------------------------------------------------------------------------------------------
// Ensure that a window does not leave the current confines of the browser.
// Parameters:
//      w: the window
//      newX: new X coordinate
//      newY: new Y coordinate
// ------------------------------------------------------------------------------------------
REDfly.fn.browserBorderCheck = function(
    w,
    newX,
    newY
) {
    var x = newX;
    var y = newY;
    if ( newX < 0 ) x = 0;
    if ( newY < 0 ) x = 0;
    if ( newX + w.getWidth() > window.width ) {
        x = window.width - w.getWidth() - 20;
    }
    if ( x != newX || y != newY ) w.setPosition(x, y);
}
// ------------------------------------------------------------------------------------------
// If a window already exists for the specified REDfly ID show it, otherwise create a new
// window. This prevents duplicate windows.
// Parameters:
//      redflyId: REDfly identifier for the entity
//      entityName: Entity name
// ------------------------------------------------------------------------------------------
REDfly.fn.showOrCreateEntityWindow = function(
    redflyId,
    entityName
) {
    var window;
    if ( (window = REDfly.state.windowGroup.get(redflyId)) ) {
        REDfly.state.windowGroup.get(redflyId).show();
    } else {
        var entityType = redflyId.match(/^RF(IC|PCRM|RC|SEG|TF)/);
        if ( ! entityType ) {
            Ext.Msg.alert(
                'showOrCreateEntityWindow',
                'Not a REDfly ID "' + redflyId + '"');
            return;
        }
        switch ( entityType[1] ) {
            case 'IC':
                // Not applicable here
                break;
            case 'PCRM':
                window = new REDfly.window.predictedCrm({
                    redflyId: redflyId,
                    entityName: entityName
                });
                break;
            case 'RC':
                window = new REDfly.window.reporterConstruct({
                    redflyId: redflyId,
                    entityName: entityName
                });
                break;
            case 'SEG':
                window = new REDfly.window.crmSegment({
                    redflyId: redflyId,
                    entityName: entityName
                });
                break;
            case 'TF':
                window = new REDfly.window.tfbs({
                    redflyId: redflyId,
                    entityName: entityName
                });
                break;
            default:
                Ext.Msg.alert(
                    'showOrCreateEntityWindow',
                    'Unsupported REDfly ID "' + redflyId + '"'
                );
                break;
        }
    }

    return window;
}