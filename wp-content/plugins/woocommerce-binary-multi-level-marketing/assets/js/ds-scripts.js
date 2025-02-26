var ds_sponsors_data;
var dataReceivedFlag = false;  // Flag to check if data is received
var dsScript = {
    // Private method to simulate data retrieval from an API
    __getChildren: function(sponsor_id) {
        jQuery.ajax({
            url: ds_bmlm.ajax_url,
            type: 'POST',
            data: {
                action: 'get_data', // Must match the PHP hook names
                ds_sponsor_id: sponsor_id
            },
            success: function(response) {
                //console.log(JSON.parse(response));
                dataReceivedFlag = true;
                dsScript.setSponsorsData(response);  // Using the setter to update data
            },
            error: function(error) {
                console.log(error);
                dataReceivedFlag = false;  // Ensure the flag is false on error
            }
        });
    },

    // Getter method to retrieve ds_sponsors_data
    __getChildData: function() {
        if (dataReceivedFlag) {
            return dsScript.getSponsorsData();  // Only return data if flag is true
        }
    },

    // Setter for ds_sponsors_data
    setSponsorsData: function(data) {
        if (dataReceivedFlag) {
            ds_sponsors_data = data;
        } else {
            console.log("Data has not been received successfully.");
        }
    },

    // Getter for ds_sponsors_data
    getSponsorsData: function() {
        return ds_sponsors_data;
    }
};
