package adriantam18.crowdcontrol.Model;

import com.google.gson.annotations.SerializedName;

/**
 * This class will contain crowd information received from the remote server.
 * The member variables represent the keys in the JSON response and will contain
 * the values for those keys
 */
public class CrowdData {

    @SerializedName("room_id")
    private String id;

    /** Room number. */
    @SerializedName("room_number")
    private String room;

    /** Date that crowd data was last updated in the server. */
    private String date;

    /** Time that crowd data was last updated in the server. */
    private String time;

    /** Rounded percentage of how crowded the room is. */
    @SerializedName("crowd")
    private int crowdPercent;

    public String getId(){
        return this.id;
    }

    public void setId(String id){
        this.id = id;
    }

    public String getRoom(){
        return this.room;
    }

    public void setRoom(String room){
        this.room = room;
    }

    public String getDate(){
        return this.date;
    }

    public void setDate(String date){
        this.date = date;
    }

    public String getTime(){
        return this.time;
    }

    public void setTime(String time){
        this.time = time;
    }

    public int getCrowdPercent(){
        return this.crowdPercent;
    }

    public void setCrowdPercent(String crowdPercent){
        try {
            this.crowdPercent = Integer.parseInt(crowdPercent);
        }catch (NumberFormatException e){
            this.crowdPercent = -1;
        }
    }
}
