package adriantam18.crowdcontrol;

public interface IResponseListener<T> {
    void onRequestSuccess(T object);
    void onRequestError(String message);
}
