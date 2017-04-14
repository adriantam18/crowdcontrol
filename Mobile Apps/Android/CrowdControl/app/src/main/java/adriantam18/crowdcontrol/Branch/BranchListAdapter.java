package adriantam18.crowdcontrol.Branch;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.TextView;

import java.util.ArrayList;
import java.util.List;

import adriantam18.crowdcontrol.Model.BranchData;
import adriantam18.crowdcontrol.R;
import butterknife.BindView;
import butterknife.ButterKnife;

/**
 * A custom adapter for displaying BranchData. Each row contains two TextViews,
 * one to display the branch address and another to display the operating hours.
 */
public class BranchListAdapter extends ArrayAdapter<BranchData> {

    static class BranchViewHolder{
        @BindView(R.id.branch_address) TextView mAddressView;
        @BindView(R.id.hours) TextView mHoursView;
        int mPosition;

        public BranchViewHolder(View view){
            ButterKnife.bind(this, view);
        }
    }

    public BranchListAdapter(Context context, ArrayList<BranchData> branches){
        super(context, 0, branches);
    }

    @Override
    public View getView(int position, View convertView, ViewGroup parent){
        BranchData branch = getItem(position);
        BranchViewHolder viewHolder;

        /**
         * Only inflate a new layout if convertView is null, otherwise use the views in the ViewHolder
         * to avoid lookups by findViewById()
         */
        if(convertView == null){
            convertView = LayoutInflater.from(getContext()).inflate(R.layout.branch_list_item, parent, false);

            viewHolder = new BranchViewHolder(convertView);
            viewHolder.mPosition = position;

            convertView.setTag(viewHolder);
        }else{
            viewHolder = (BranchViewHolder) convertView.getTag();
        }

        viewHolder.mAddressView.setText(branch.getAddress());
        viewHolder.mHoursView.setText(branch.getOpenHours() + " - " + branch.getCloseHours());
        return convertView;
    }

    public void replaceData(List<BranchData> branches){
        this.clear();
        this.addAll(branches);
        this.notifyDataSetChanged();
    }
}
