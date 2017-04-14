package adriantam18.crowdcontrol.Model;

import com.google.gson.annotations.SerializedName;

/**
 * This class will contain branch information received from the remote server.
 * The member variables represent the keys in the JSON response and will contain
 * the values for those keys
 */
public class BranchData {

    /** The id of this branch in the remote database. */
    @SerializedName("branch_id")
    private String id;

    /** The address where this branch is located. */
    private String address;

    private String zipcode;

    /** Opening hours of this branch. */
    @SerializedName("open_hours")
    private String openHours;

    /** Closing hours of this branch. */
    @SerializedName("close_hours")
    private String closeHours;

    /** Distance of this branch to another location. */
    private String distance;

    /** Latitude of this branch. */
    private String lat;

    /** Longitude of this branch. */
    private String lng;

    public String getId(){
        return this.id;
    }

    public void setId(String id) {
        this.id = id;
    }

    public String getAddress(){
        return this.address;
    }
    
    public void setAddress(String address){
        this.address = address;
    }

    public String getZipCode(){
        return this.zipcode;
    }

    public void setZipCode(String zipCode) {
        this.zipcode = zipCode;
    }

    public String getOpenHours(){
        return this.openHours;
    }

    public void setOpenHours(String openHours) {
        this.openHours = openHours;
    }

    public String getCloseHours(){
        return this.closeHours;
    }

    public void setCloseHours(String closeHours) {
        this.closeHours = closeHours;
    }

    public String getDistance(){
        return this.distance;
    }

    public void setDistance(String distance) {
        this.distance = distance;
    }

    public String getLat(){
        return this.lat;
    }

    public void setLat(String lat) {
        this.lat = lat;
    }

    public String getLng(){
        return this.lng;
    }

    public void setLng(String lng) {
        this.lng = lng;
    }
}
