package adriantam18.crowdcontrol.Model;

import com.google.gson.annotations.SerializedName;

/**
 * This class will contain company information received from the remote server.
 * The member variables represent the keys in the JSON response and will contain
 * the values for those keys
 */
public class CompanyData {

    @SerializedName("company_id")
    private String id;

    /** Name of the company. */
    @SerializedName("company_name")
    private String name;

    /** Type of establishment (school, restaurant, etc.). */
    private String type;

    public String getId(){
        return this.id;
    }

    public void setId(String id){
        this.id = id;
    }

    public String getName(){
        return this.name;
    }

    public void setName(String name){
        this.name = name;
    }

    public String getType(){
        return this.type;
    }

    public void setType(String type){
        this.type = type;
    }
}
