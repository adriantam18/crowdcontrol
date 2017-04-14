package adriantam18.crowdcontrol.Crowd;

import android.content.Context;
import android.database.DataSetObserver;
import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseExpandableListAdapter;
import android.widget.TextView;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import adriantam18.crowdcontrol.Model.CrowdData;
import adriantam18.crowdcontrol.R;
import butterknife.BindView;
import butterknife.ButterKnife;

/**
 * A custom adapter for displaying Crowd Data. The headers are the room numbers.
 * The children will be the crowd information (i.e. crowd percentage, date, time).
 */
public class CustomExpListAdapter extends BaseExpandableListAdapter {

    /** Context where the adapter is being used. */
    private Context mContext;

    /** Strings to be displayed on the top level. */
    private List<String> mHeaders;

    /** A mapping of a header string to the list of strings displayed under that header. */
    private HashMap<String, List<String>> mChildren;

    static class HeaderViewHolder{
        @BindView(R.id.room_header) TextView mHeaderView;

        public HeaderViewHolder(View view){
            ButterKnife.bind(this, view);
        }
    }

    static class ChildViewHolder{
        @BindView(R.id.room_data) TextView mChildView;

        public ChildViewHolder(View view){
            ButterKnife.bind(this, view);
        }
    }

    public CustomExpListAdapter(Context context, List<String> headers, HashMap<String, List<String>> children){
        mContext = context;
        mHeaders = headers;
        mChildren = children;
    }

    @Override
    public Object getChild(int groupPosition, int childPosition){
        return mChildren.get(mHeaders.get(groupPosition)).get(childPosition);
    }

    @Override
    public long getChildId(int groupPosition, int childPosition){
        return childPosition;
    }

    @Override
    public View getChildView(int groupPosition, int childPosition, boolean isLastChild, View convertView, ViewGroup parent){
        String text = (String) getChild(groupPosition, childPosition);
        ChildViewHolder viewHolder;

        if(convertView == null){
            LayoutInflater inflater = (LayoutInflater) mContext.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            convertView = inflater.inflate(R.layout.activity_crowd_items, null);

            viewHolder = new ChildViewHolder(convertView);

            convertView.setTag(viewHolder);
        }else{
            viewHolder = (ChildViewHolder) convertView.getTag();
        }

        viewHolder.mChildView.setText(text);

        return convertView;
    }

    @Override
    public int getChildrenCount(int groupPosition){
        return mChildren.get(mHeaders.get(groupPosition)).size();
    }

    @Override
    public Object getGroup(int groupPosition){
        return mHeaders.get(groupPosition);
    }

    @Override
    public long getGroupId(int groupPosition){
        return groupPosition;
    }

    @Override
    public int getGroupCount(){
        return mHeaders.size();
    }

    @Override
    public View getGroupView(int groupPosition, boolean isExpanded, View convertView, ViewGroup parent){
        String title = (String) getGroup(groupPosition);
        HeaderViewHolder viewHolder;

        if(convertView == null){
            LayoutInflater inflater = (LayoutInflater) mContext.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            convertView = inflater.inflate(R.layout.activity_crowd_headers, null);

            viewHolder = new HeaderViewHolder(convertView);

            convertView.setTag(viewHolder);
        }else{
            viewHolder = (HeaderViewHolder) convertView.getTag();
        }

        viewHolder.mHeaderView.setText(title);
        viewHolder.mHeaderView.setTextColor(Color.WHITE);
        viewHolder.mHeaderView.setBackgroundColor(Color.BLUE);

        return convertView;
    }

    @Override
    public boolean hasStableIds(){
        return false;
    }

    @Override
    public boolean isChildSelectable(int groupPosition, int childPosition){
        return false;
    }

    @Override
    public void registerDataSetObserver(DataSetObserver dataSetObserver){
        super.registerDataSetObserver(dataSetObserver);
    }

    public void replaceData(List<CrowdData> crowdDataList){
        mHeaders.clear();
        mChildren.clear();

        for (CrowdData crowdData : crowdDataList) {
            ArrayList<String> addInfo = new ArrayList<>();
            addInfo.add("Crowd: " + Integer.toString(crowdData.getCrowdPercent()) + "%");
            addInfo.add("Date: " + crowdData.getDate());
            addInfo.add("Time: " + crowdData.getTime());
            mChildren.put(crowdData.getRoom(), addInfo);
            mHeaders.add(crowdData.getRoom());
        }

        this.notifyDataSetChanged();
    }
}
