package adriantam18.crowdcontrol.Branch;

import adriantam18.crowdcontrol.BaseView;
import adriantam18.crowdcontrol.Model.BranchData;

public interface BranchView extends BaseView<BranchData>{
    void showRooms(BranchData branchData);
}
