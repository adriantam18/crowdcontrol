package adriantam18.crowdcontrol.Company;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.TextView;

import java.util.ArrayList;
import java.util.List;

import adriantam18.crowdcontrol.Model.CompanyData;
import adriantam18.crowdcontrol.R;
import butterknife.BindView;
import butterknife.ButterKnife;

/**
 * A custom adapter for displaying company information. Each row has two TextViews,
 * one to display the company name and another to display company type (school, restaurant, etc.)
 */
public class CompanyListAdapter extends ArrayAdapter<CompanyData> {

    /**
     * A class that will hold views to make scrolling the list view smoother
     */
    static class CompanyViewHolder{
        @BindView(R.id.company_name) TextView mNameView;
        @BindView(R.id.company_type) TextView mTypeView;
        int mPosition;

        public CompanyViewHolder(View view){
            ButterKnife.bind(this, view);
        }
    }

    public CompanyListAdapter(Context context, ArrayList<CompanyData> companies){
        super(context, 0, companies);
    }

    @Override
    public View getView(int position, View convertView, ViewGroup parent){
        CompanyData companyData = getItem(position);
        CompanyViewHolder viewHolder;

        /**
         * Only inflate a new layout if convertView is null, otherwise use the views in the ViewHolder
         * to avoid lookups by findViewById()
         */
        if(convertView == null){
            convertView = LayoutInflater.from(getContext()).inflate(R.layout.company_list_item, parent, false);

            viewHolder = new CompanyViewHolder(convertView);
            viewHolder.mPosition = position;

            convertView.setTag(viewHolder);
        }else{
            viewHolder = (CompanyViewHolder) convertView.getTag();
        }

        viewHolder.mNameView.setText(companyData.getName());
        viewHolder.mTypeView.setText(companyData.getType());

        return convertView;
    }

    public void replaceData(List<CompanyData> data){
        this.clear();
        this.addAll(data);
        this.notifyDataSetChanged();
    }
}

