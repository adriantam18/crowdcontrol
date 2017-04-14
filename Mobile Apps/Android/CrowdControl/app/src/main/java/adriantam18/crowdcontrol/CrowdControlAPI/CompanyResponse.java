package adriantam18.crowdcontrol.CrowdControlAPI;

import java.util.List;

import adriantam18.crowdcontrol.Model.CompanyData;

public class CompanyResponse {
    List<CompanyData> data;

    public List<CompanyData> getData(){
        return this.data;
    }

    public void setData(List<CompanyData> data){
        this.data = data;
    }
}
