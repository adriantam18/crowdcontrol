package adriantam18.crowdcontrol.Company;

import adriantam18.crowdcontrol.BaseView;
import adriantam18.crowdcontrol.Model.CompanyData;

/**
 * Created by apdt_18 on 12/22/2016.
 */

public interface CompanyView extends BaseView<CompanyData> {
    void showBranches(String companyName);
}
