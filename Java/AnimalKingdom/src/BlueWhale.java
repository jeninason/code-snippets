
public class BlueWhale extends Whale implements Endangered {

	public static final String BLUE_WHALE_DESCRIPTION = "Blue Whale";
	private boolean isEndangered;
	
	public BlueWhale(int id, String name) {
		super(id, name);
		this.isEndangered = true;
	}

	@Override
	public String getDescription() {
		return super.getDescription() + BLUE_WHALE_DESCRIPTION
				+ ((isEndangered) ? " (endangered)" : "");
	}

	@Override
	public void displayConservationInformation() {
		System.out.print("Help save the mighty " + BLUE_WHALE_DESCRIPTION + "!");
	}

}
