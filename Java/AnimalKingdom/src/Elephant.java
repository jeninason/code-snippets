
public class Elephant extends Mammal implements Endangered{
	
	public static final String ELEPHANT_DESCRIPTION = "Elephant";
	private boolean isEndangered;
	
	public Elephant(int id, String name) {
		super(id, name);
		this.isEndangered = true;
	}
	
	@Override
    public String getDescription() {
        return super.getDescription() + ELEPHANT_DESCRIPTION +  " " + ((isEndangered) ? "(endangered)":"");
    }
	
	@Override
	public void displayConservationInformation() {
		System.out.print("Help save the beautiful " + ELEPHANT_DESCRIPTION + "!");
	}

}
