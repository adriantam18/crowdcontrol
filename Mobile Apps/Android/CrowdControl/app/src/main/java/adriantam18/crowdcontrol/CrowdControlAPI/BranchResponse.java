package adriantam18.crowdcontrol.CrowdControlAPI;

import java.util.List;

import adriantam18.crowdcontrol.Model.BranchData;

public class BranchResponse {
    List<BranchData> data;

    public List<BranchData> getData(){
        return this.data;
    }

    public void setData(List<BranchData> data){
        this.data = data;
    }
}
