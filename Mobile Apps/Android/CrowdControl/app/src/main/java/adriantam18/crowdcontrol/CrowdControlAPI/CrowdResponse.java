package adriantam18.crowdcontrol.CrowdControlAPI;

import java.util.List;

import adriantam18.crowdcontrol.Model.CrowdData;

public class CrowdResponse {
    List<CrowdData> data;

    public List<CrowdData> getData(){
        return this.data;
    }

    public void setData(List<CrowdData> data){
        this.data = data;
    }
}
