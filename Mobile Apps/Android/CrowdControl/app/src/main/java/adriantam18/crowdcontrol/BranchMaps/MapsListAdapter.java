package adriantam18.crowdcontrol.BranchMaps;

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

public class MapsListAdapter extends ArrayAdapter<BranchData> {

    public MapsListAdapter(Context context, ArrayList<BranchData> distances){
        super(context, 0, distances);
    }

    static class MapsViewHolder{
        @BindView(R.id.branch_address) TextView addressView;
        @BindView(R.id.hours) TextView hoursView;
        @BindView(R.id.distance) TextView distanceView;

        public MapsViewHolder(View view){
            ButterKnife.bind(this, view);
        }
    }

    @Override
    public View getView(int position, View view, ViewGroup parent){
        MapsViewHolder viewHolder;
        BranchData branchData = getItem(position);

        if(view == null){
            view = LayoutInflater.from(getContext()).inflate(R.layout.maps_list_item, parent, false);

            viewHolder = new MapsViewHolder(view);

            view.setTag(viewHolder);
        }else{
            viewHolder = (MapsViewHolder) view.getTag();
        }

        viewHolder.addressView.setText(Integer.toString(position + 1) + ". " + branchData.getAddress());
        viewHolder.hoursView.setText(branchData.getOpenHours() + " - " + branchData.getCloseHours());
        String distance = branchData.getDistance() + " miles";
        viewHolder.distanceView.setText(distance);

        return view;
    }

    public void replaceData(List<BranchData> branches){
        this.clear();
        this.addAll(branches);
        this.notifyDataSetChanged();
    }
}
