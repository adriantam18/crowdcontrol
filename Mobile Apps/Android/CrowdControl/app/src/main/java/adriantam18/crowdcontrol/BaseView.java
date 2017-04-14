package adriantam18.crowdcontrol;

import java.util.List;

public interface BaseView<T> {
    void showData(List<T> data);
    void showError(String message);
}
